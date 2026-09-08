<?php

/**
 * Facebook Lead Fetcher for Prefex CRM
 * Single file solution - Fetch leads from Facebook and add to tblleads
 * Run via cron every 5 minutes
 * 
 * UPDATED: Fixed Facebook API issues
 * UPDATED: Added round-robin assignment for form 1265188688846520
 * UPDATED: Added automatic task creation for assigned leads
 * UPDATED: Fixed database connection access issue
 */

// ============================================
// CONFIGURATION
// ============================================

// Facebook API Configuration
define('FB_APP_ID', '1486823046543130');
define('FB_APP_SECRET', '165fddbb5fa701b62ae0ee7b98441eb8');
define('FB_PAGE_ID', '385099078011732');
define('FB_GRAPH_VERSION', 'v18.0');

// IMPORTANT: Put your LONG-LIVED USER ACCESS TOKEN here
define('FB_LONG_LIVED_USER_TOKEN', '');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medawkexports');

// Application Settings
define('MAX_LEADS_PER_FETCH', 100);
define('LOG_FILE', 'facebook_leads.log');
define('TIMEZONE', 'UTC');

// Task Creation Settings
define('CREATE_TASK_FOR_LEADS', true);  // Set to false to disable task creation
define('TASK_PRIORITY', 3);  // 1=Low, 2=Medium, 3=High, 4=Urgent
define('TASK_STATUS', 1);    // 1=Not Started, 2=In Progress, 3=Completed, 4=Waiting for feedback

// Form selection
define('FETCH_ALL_FORMS', true);
define('ALLOWED_FORM_IDS', [
    '1287265923224905',
    '1265188688846520',
    '1106957472018671',
]);

// ============================================
// DATE FILTERING CONFIGURATION
// ============================================

/**
 * DATE_FILTER_TYPE Options:
 * - 'LAST_RUN'      : Fetch leads since last script execution (recommended)
 * - 'LAST_24_HOURS' : Fetch leads from last 24 hours
 * - 'LAST_7_DAYS'   : Fetch leads from last 7 days
 * - 'LAST_30_DAYS'  : Fetch leads from last 30 days
 * - 'TODAY'         : Fetch leads from today only
 * - 'YESTERDAY'     : Fetch leads from yesterday only
 * - 'CUSTOM'        : Use custom start and end date (define below)
 * - 'ALL'           : Fetch all leads (no date filter)
 */
define('DATE_FILTER_TYPE', 'LAST_24_HOURS');

// Custom date range (only used if DATE_FILTER_TYPE is 'CUSTOM')
define('CUSTOM_START_DATE', '2026-09-01 00:00:00');
define('CUSTOM_END_DATE', '2026-09-07 23:59:59');

// File to store last run timestamp (for LAST_RUN mode)
define('LAST_RUN_FILE', 'last_run.txt');

// ============================================
// FORM FIELD MAPPING CONFIGURATION
// ============================================
define('FORM_FIELD_MAPPING', [

    '1106957472018671' => [
        'projects' => 7,
        'assigned' => 28,  // Always assign to user 28
    ],

    '1287265923224905' => [
        'projects' => 4,
        'assigned' => 30,  // Always assign to user 30
    ],

    '1265188688846520' => [
        'projects' => 1,
        // 'assigned' will be handled by RoundRobinAssigner
    ],

    '*' => [
        'status' => 16,
        'source' => 2,
    ]
]);

// Status mapping
define('LEAD_STATUS_NEW', 1);
define('LEAD_STATUS_CONTACTED', 2);
define('LEAD_STATUS_QUALIFIED', 3);
define('LEAD_STATUS_CONVERTED', 4);
define('LEAD_STATUS_LOST', 5);

// Source mapping
define('LEAD_SOURCE_FACEBOOK', 2);

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error.log');
error_reporting(E_ALL);

// Memory and execution limits
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

// ============================================
// DATABASE CLASS
// ============================================

class Database
{
    private $connection;
    private $lastInsertId;
    private $error;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            $this->logError("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }

    // Getter method for connection
    public function getConnection()
    {
        return $this->connection;
    }

    public function escapeString($string)
    {
        return $this->connection->real_escape_string($string);
    }

    public function insert($table, $data)
    {
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        $fields = array_keys($data);
        $values = array_map(function ($value) {
            return "'" . $this->escapeString($value) . "'";
        }, array_values($data));

        $query = "INSERT INTO {$table} (" . implode(', ', $fields) . ") 
                  VALUES (" . implode(', ', $values) . ")";

        if ($this->connection->query($query)) {
            $this->lastInsertId = $this->connection->insert_id;
            return $this->lastInsertId;
        }

        $this->error = $this->connection->error;
        $this->logError("Insert error: " . $this->connection->error . " Query: " . $query);
        return false;
    }

    public function checkExistingLead($email, $phonenumber = null)
    {
        $query = "SELECT id FROM tblleads WHERE email = '" . $this->escapeString($email) . "'";

        if ($phonenumber) {
            $query .= " OR phonenumber = '" . $this->escapeString($phonenumber) . "'";
        }

        $result = $this->connection->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }

        return false;
    }

    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }

    public function getError()
    {
        return $this->error;
    }

    public function isConnected()
    {
        return $this->connection ? true : false;
    }

    private function logError($message)
    {
        error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, LOG_FILE);
    }

    public function __destruct()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// ============================================
// TASK CREATOR CLASS
// ============================================

class TaskCreator
{
    private $db;
    private $logFile;

    public function __construct($db)
    {
        $this->db = $db;
        $this->logFile = LOG_FILE;
    }

    /**
     * Create a task for a lead
     */
    public function createTaskForLead($leadId, $leadData, $assignedTo)
    {
        try {
            // Check if task already exists for this lead today
            $existingTask = $this->checkExistingTask($leadId);
            if ($existingTask) {
                $this->logMessage("Task already exists for lead {$leadId} (Task ID: {$existingTask})");
                return $existingTask;
            }

            // Prepare task data
            $taskName = $leadData['name'] ?? 'New Lead';

            $taskData = [
                'name' => $taskName,
                'is_public' => 1,
                'startdate' => date('Y-m-d'),
                'duedate' => date('Y-m-d'),
                'priority' => TASK_PRIORITY,
                'rel_type' => 'lead',
                'rel_id' => $leadId,
                'status' => TASK_STATUS,
                'dateadded' => date('Y-m-d H:i:s'),
                'addedfrom' => 1, // Default admin user
                'visible_to_client' => 1,
                'billable' => 0,
                'milestone' => 0,
                'recurring' => 0
            ];

            // Insert task
            $taskId = $this->insertTask($taskData);

            if ($taskId) {

                // Add assignee
                $assigneeAdded = $this->addTaskAssignee($taskId, $assignedTo);

                if ($assigneeAdded) {
                    $this->logMessage(
                        "Task created and assigned successfully - Lead ID: {$leadId}, " .
                            "Task ID: {$taskId}, Staff ID: {$assignedTo}"
                    );
                } else {
                    $this->logMessage(
                        "Task created BUT assignment failed - Lead ID: {$leadId}, " .
                            "Task ID: {$taskId}, Staff ID: {$assignedTo}"
                    );
                }

                return $taskId;
            }

            return false;
        } catch (Exception $e) {
            $this->logMessage("Error creating task for lead {$leadId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a task already exists for this lead today
     */
    private function checkExistingTask($leadId)
    {
        $today = date('Y-m-d');

        $query = "SELECT id FROM tbltasks 
                  WHERE rel_id = '" . $this->db->escapeString($leadId) . "' 
                  AND rel_type = 'lead' 
                  AND duedate = '" . $this->db->escapeString($today) . "'";

        // Use getConnection() method to access the connection
        $result = $this->db->getConnection()->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }

        return false;
    }

    /**
     * Insert task into database
     */
    private function insertTask($taskData)
    {
        // Prepare data for insertion
        $insertData = [
            'name' => $taskData['name'],
            'is_public' => $taskData['is_public'],
            'startdate' => $taskData['startdate'],
            'duedate' => $taskData['duedate'],
            'priority' => $taskData['priority'],
            'rel_type' => $taskData['rel_type'],
            'rel_id' => $taskData['rel_id'],
            'status' => $taskData['status'],
            'dateadded' => $taskData['dateadded'],
            'addedfrom' => $taskData['addedfrom'],
            'description' => $taskData['description'] ?? '',
            'visible_to_client' => $taskData['visible_to_client'] ?? 1,
            'billable' => $taskData['billable'] ?? 0,
            'milestone' => $taskData['milestone'] ?? 0,
            'recurring' => $taskData['recurring'] ?? 0,
            'repeat_every' => null,
            'recurring_type' => null,
            'custom_recurring' => 0,
            'is_added_from_contact' => 0
        ];

        return $this->db->insert('tbltasks', $insertData);
    }

    /**
     * Add assignee to task
     */
    /**
     * Add assignee to task
     */
    private function addTaskAssignee($taskId, $staffId)
    {
        try {

            $data = [
                'taskid' => (int)$taskId,
                'staffid' => (int)$staffId
            ];

            $result = $this->db->insert('tbltask_assigned', $data);

            if ($result) {
                $this->logMessage(
                    "Task assignee added successfully - Task ID: {$taskId}, Staff ID: {$staffId}"
                );

                return true;
            }

            $error = $this->db->getError();

            $this->logMessage(
                "FAILED to add task assignee - Task ID: {$taskId}, Staff ID: {$staffId}, Error: {$error}"
            );

            return false;
        } catch (Exception $e) {

            $this->logMessage(
                "Exception adding task assignee - Task ID: {$taskId}, Staff ID: {$staffId}, Error: " .
                    $e->getMessage()
            );

            return false;
        }
    }

    private function logMessage($message)
    {
        error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, $this->logFile);
    }
}

// ============================================
// ROUND-ROBIN ASSIGNMENT CLASS
// ============================================

class RoundRobinAssigner
{
    private static $userPool = [31, 32];
    private static $lastAssigned = [];
    private static $totalAssigned = [31 => 0, 32 => 0];

    /**
     * Get the next user ID using round-robin for a specific form
     */
    public static function getNextUser($formId)
    {
        // For form 1265188688846520, use round-robin between 31 and 32
        if ($formId === '1265188688846520') {
            // Initialize counter for this form if not exists
            if (!isset(self::$lastAssigned[$formId])) {
                self::$lastAssigned[$formId] = 0;
            }

            // Get next user
            $nextUser = self::$userPool[self::$lastAssigned[$formId] % count(self::$userPool)];

            // Increment counter for next time
            self::$lastAssigned[$formId]++;

            // Track total assigned
            self::$totalAssigned[$nextUser]++;

            return $nextUser;
        }

        // For other forms, return 0 (no assignment)
        return 0;
    }

    /**
     * Get assignment statistics
     */
    public static function getStats()
    {
        return self::$totalAssigned;
    }

    /**
     * Reset counter for a specific form (optional)
     */
    public static function resetCounter($formId)
    {
        if (isset(self::$lastAssigned[$formId])) {
            self::$lastAssigned[$formId] = 0;
        }
    }
}

// ============================================
// FACEBOOK API CLASS (WITH FIXES)
// ============================================

class FacebookLeadAPI
{
    private $accessToken;
    private $graphVersion;
    private $lastError;
    private $dateFilter;

    public function __construct()
    {
        $this->graphVersion = FB_GRAPH_VERSION;

        // Get Page Access Token
        $pageToken = $this->getPageAccessToken();

        if (!$pageToken) {
            throw new Exception("Could not obtain Page Access Token. Please check your Long-Lived User Token.");
        }

        $this->accessToken = $pageToken;
        $this->dateFilter = $this->getDateFilter();
    }

    private function getPageAccessToken()
    {
        // REMOVED leads_count from fields as it causes error
        $url = "https://graph.facebook.com/" . FB_GRAPH_VERSION . "/" . FB_PAGE_ID .
            "?fields=access_token,name&access_token=" . urlencode(FB_LONG_LIVED_USER_TOKEN);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['access_token'])) {
            $this->logMessage("Successfully obtained Page Access Token automatically");
            return $data['access_token'];
        }

        $errorMsg = $data['error']['message'] ?? $response;
        $this->logError("Failed to get Page Access Token: " . $errorMsg);
        return false;
    }

    private function getDateFilter()
    {
        $filterType = DATE_FILTER_TYPE;
        $filter = ['start' => null, 'end' => null];

        switch ($filterType) {
            case 'LAST_RUN':
                $lastRun = $this->getLastRunTime();
                if ($lastRun) {
                    $filter['start'] = $lastRun;
                    $filter['end'] = date('Y-m-d H:i:s');
                    $this->logMessage("Date Filter: LAST_RUN (from {$lastRun} to now)");
                } else {
                    $filter['start'] = date('Y-m-d H:i:s', strtotime('-24 hours'));
                    $filter['end'] = date('Y-m-d H:i:s');
                    $this->logMessage("Date Filter: FIRST_RUN - using last 24 hours");
                }
                break;

            case 'LAST_24_HOURS':

                $now = time();
                $last24Hours = $now - (24 * 60 * 60);

                $filter['start'] = date('Y-m-d H:i:s', $last24Hours);
                $filter['end'] = date('Y-m-d H:i:s', $now);

                $this->logMessage(
                    "Date Filter: LAST_24_HOURS | " .
                        $filter['start'] . " to " . $filter['end']
                );

                break;

            case 'LAST_7_DAYS':
                $filter['start'] = date('Y-m-d H:i:s', strtotime('-7 days'));
                $filter['end'] = date('Y-m-d H:i:s');
                $this->logMessage("Date Filter: LAST_7_DAYS");
                break;

            case 'LAST_30_DAYS':
                $filter['start'] = date('Y-m-d H:i:s', strtotime('-30 days'));
                $filter['end'] = date('Y-m-d H:i:s');
                $this->logMessage("Date Filter: LAST_30_DAYS (from {$filter['start']} to {$filter['end']})");
                break;

            case 'TODAY':
                $filter['start'] = date('Y-m-d 00:00:00');
                $filter['end'] = date('Y-m-d 23:59:59');
                $this->logMessage("Date Filter: TODAY");
                break;

            case 'YESTERDAY':
                $filter['start'] = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $filter['end'] = date('Y-m-d 23:59:59', strtotime('-1 day'));
                $this->logMessage("Date Filter: YESTERDAY");
                break;

            case 'CUSTOM':
                $filter['start'] = CUSTOM_START_DATE;
                $filter['end'] = CUSTOM_END_DATE;
                $this->logMessage("Date Filter: CUSTOM ({$filter['start']} to {$filter['end']})");
                break;

            case 'ALL':
            default:
                $filter['start'] = null;
                $filter['end'] = null;
                $this->logMessage("Date Filter: ALL (no filter)");
                break;
        }

        return $filter;
    }

    private function getLastRunTime()
    {
        if (file_exists(LAST_RUN_FILE)) {
            $timestamp = file_get_contents(LAST_RUN_FILE);
            return trim($timestamp);
        }
        return null;
    }

    public function saveLastRunTime()
    {
        file_put_contents(LAST_RUN_FILE, date('Y-m-d H:i:s'));
    }

    public function getForms($pageId, $limit = 50)
    {
        // REMOVED leads_count from fields - FIXED
        $url = "https://graph.facebook.com/{$this->graphVersion}/{$pageId}/leadgen_forms";
        $params = [
            'access_token' => $this->accessToken,
            'limit' => $limit,
            'fields' => 'id,name,status,created_time,page_id'  // REMOVED leads_count
        ];
        $fullUrl = $url . '?' . http_build_query($params);
        $this->logMessage("Fetching forms from: " . $fullUrl);
        return $this->makeRequest($fullUrl);
    }

    public function getLeads($formId, $limit = 100, $after = null)
    {

        $url = "https://graph.facebook.com/{$this->graphVersion}/{$formId}/leads";

        $params = [
            'access_token' => $this->accessToken,
            'limit' => $limit,
            'fields' => 'id,created_time,field_data,ad_id,ad_name,adset_id,adset_name,page_id,form_id'
        ];

        /*
     * Facebook Lead Ads API date filtering
     *
     * IMPORTANT:
     * Filter field is "time_created", NOT "created_time".
     */

        if ($this->dateFilter['start']) {

            $startTimestamp = strtotime($this->dateFilter['start']);

            $params['filtering'] = json_encode([
                [
                    'field' => 'time_created',
                    'operator' => 'GREATER_THAN',
                    'value' => $startTimestamp
                ]
            ]);

            $this->logMessage(
                "Facebook date filter applied: " .
                    $this->dateFilter['start'] .
                    " (" . $startTimestamp . ")"
            );
        }

        if ($after) {
            $params['after'] = $after;
        }

        $fullUrl = $url . '?' . http_build_query($params);

        $this->logMessage("Fetching leads from: " . $fullUrl);

        return $this->makeRequest($fullUrl);
    }

    private function makeRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        curl_close($ch);

        // Log response for debugging
        $this->logMessage("Response HTTP Code: {$httpCode}");

        if ($httpCode !== 200) {
            $this->lastError = "HTTP Error: {$httpCode}";
            $this->logError("Facebook API HTTP error: {$httpCode} - " . substr($body, 0, 500));

            // Parse error message if available
            $errorData = json_decode($body, true);
            if (isset($errorData['error'])) {
                $this->lastError .= " - " . ($errorData['error']['message'] ?? 'Unknown error');
                $this->logError("Facebook API error details: " . print_r($errorData['error'], true));
            }

            return false;
        }

        $data = json_decode($body, true);

        if (isset($data['error'])) {
            $this->lastError = $data['error']['message'];
            $this->logError("Facebook API error: " . $data['error']['message']);
            return false;
        }

        return $data;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getDateFilterInfo()
    {
        return $this->dateFilter;
    }

    private function logMessage($message)
    {
        error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, LOG_FILE);
    }

    private function logError($message)
    {
        error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, LOG_FILE);
    }
}

// ============================================
// LEAD PROCESSOR CLASS
// ============================================

class LeadProcessor
{
    private $db;
    private $processedCount = 0;
    private $duplicateCount = 0;
    private $failedCount = 0;
    private $taskCreatedCount = 0;
    private $taskFailedCount = 0;
    private $errors = [];
    private $formMapping;
    private $assignmentStats = [];
    private $taskCreator;

    public function __construct($db)
    {
        $this->db = $db;
        $this->formMapping = defined('FORM_FIELD_MAPPING') ? FORM_FIELD_MAPPING : [];
        $this->assignmentStats = [
            'user_28' => 0,
            'user_30' => 0,
            'user_31' => 0,
            'user_32' => 0,
            'unassigned' => 0
        ];

        // Initialize task creator if enabled
        if (CREATE_TASK_FOR_LEADS) {
            $this->taskCreator = new TaskCreator($this->db);
        }
    }

    public function processLead($lead)
    {
        try {
            $leadData = $this->parseLeadData($lead);

            // Check for duplicates
            $existingId = $this->db->checkExistingLead(
                $leadData['email'] ?? '',
                $leadData['phonenumber'] ?? ''
            );

            if ($existingId) {
                $this->duplicateCount++;
                $this->logMessage("Lead already exists (ID: {$existingId}) - Skipping");
                return true;
            }

            // Insert into CRM
            $leadId = $this->insertIntoCRM($leadData);

            if ($leadId) {
                // Track assignment
                $assignedUser = $leadData['assigned'] ?? 0;
                $formId = $lead['form_id'] ?? 'unknown';

                if ($assignedUser == 28) {
                    $this->assignmentStats['user_28']++;
                } elseif ($assignedUser == 30) {
                    $this->assignmentStats['user_30']++;
                } elseif ($assignedUser == 31) {
                    $this->assignmentStats['user_31']++;
                } elseif ($assignedUser == 32) {
                    $this->assignmentStats['user_32']++;
                } else {
                    $this->assignmentStats['unassigned']++;
                }

                // Create task for the lead if assigned to a user
                if (CREATE_TASK_FOR_LEADS && $assignedUser > 0) {
                    $taskId = $this->taskCreator->createTaskForLead($leadId, $leadData, $assignedUser);
                    if ($taskId) {
                        $this->taskCreatedCount++;
                    } else {
                        $this->taskFailedCount++;
                    }
                }

                $this->processedCount++;
                $this->logMessage("Lead {$lead['id']} successfully added to CRM with ID: {$leadId} (Assigned to user: {$assignedUser}, Form: {$formId})");
                return true;
            } else {
                $this->failedCount++;
                $this->errors[] = "Failed to insert lead {$lead['id']}: " . $this->db->getError();
                return false;
            }
        } catch (Exception $e) {
            $this->failedCount++;
            $this->errors[] = "Error processing lead {$lead['id']}: " . $e->getMessage();
            $this->logMessage("Error processing lead {$lead['id']}: " . $e->getMessage());
            return false;
        }
    }

    private function parseLeadData($lead)
    {
        $formId = $lead['form_id'] ?? '';
        $formFields = $this->getFormFields($formId);

        $data = [
            'facebook_lead_id' => $lead['id'],
            'dateadded' => date('Y-m-d H:i:s', strtotime($lead['created_time'])),
            'ad_id' => $lead['ad_id'] ?? null,
            'ad_name' => $lead['ad_name'] ?? null,
            'adset_id' => $lead['adset_id'] ?? null,
            'adset_name' => $lead['adset_name'] ?? null,
            'page_id' => $lead['page_id'] ?? null,
            'form_id' => $formId,
            'status' => 16,
            'source' => LEAD_SOURCE_FACEBOOK,
            'addedfrom' => 1,
            'is_public' => 0,
            'junk' => 0,
            'lost' => 0,
            'leadorder' => 1,
            'duplicate' => 0,
            'hash' => md5($lead['id'] . time()),
            'projects' => $formFields['projects'] ?? null,
            'interested_in' => $formFields['interested_in'] ?? null,
            'facing_preference' => $formFields['facing_preference'] ?? null,
            'budget' => $formFields['budget'] ?? null,
            'call_time' => $formFields['call_time'] ?? null,
            'lead_value' => $formFields['lead_value'] ?? null,
            'assigned' => $formFields['assigned'] ?? 0,
            'company' => $formFields['company'] ?? null,
            'title' => $formFields['title'] ?? null,
            'firm' => $formFields['firm'] ?? null
        ];

        if (isset($lead['field_data']) && is_array($lead['field_data'])) {
            foreach ($lead['field_data'] as $field) {
                $fieldName = strtolower($field['name']);
                $fieldValue = $field['values'][0] ?? '';

                switch ($fieldName) {
                    case 'full_name':
                    case 'name':
                        $data['name'] = $fieldValue;
                        break;
                    case 'first_name':
                        $data['first_name'] = $fieldValue;
                        break;
                    case 'last_name':
                        $data['last_name'] = $fieldValue;
                        break;
                    case 'email':
                    case 'email_address':
                        $data['email'] = $fieldValue;
                        break;
                    case 'phone_number':
                    case 'phone':
                    case 'mobile':
                    case 'mobile_number':
                        $data['phonenumber'] = $fieldValue;
                        break;
                    case 'alt_phone':
                    case 'alternate_phone':
                        $data['alt_phonenumber'] = $fieldValue;
                        break;
                    case 'country':
                        $data['country'] = $fieldValue;
                        break;
                    case 'city':
                        $data['city'] = $fieldValue;
                        break;
                    case 'state':
                    case 'province':
                        $data['state'] = $fieldValue;
                        break;
                    case 'postal_code':
                    case 'zip_code':
                    case 'zip':
                        $data['zip'] = $fieldValue;
                        break;
                    case 'address':
                    case 'street_address':
                        $data['address'] = $fieldValue;
                        break;
                    case 'company':
                    case 'firm':
                        if (empty($data['company'])) {
                            $data['company'] = $fieldValue;
                            $data['firm'] = $fieldValue;
                        }
                        break;
                    case 'title':
                    case 'job_title':
                        if (empty($data['title'])) {
                            $data['title'] = $fieldValue;
                        }
                        break;
                    case 'website':
                        $data['website'] = $fieldValue;
                        break;
                    case 'description':
                    case 'notes':
                        $data['description'] = $fieldValue;
                        break;
                    case 'budget':
                        if (empty($data['budget'])) {
                            $data['budget'] = $fieldValue;
                        }
                        break;
                    case 'interested_in':
                        if (empty($data['interested_in'])) {
                            $data['interested_in'] = $fieldValue;
                        }
                        break;
                    case 'projects':
                        if (empty($data['projects'])) {
                            $data['projects'] = $fieldValue;
                        }
                        break;
                }
            }
        }

        if (empty($data['name']) && !empty($data['first_name']) && !empty($data['last_name'])) {
            $data['name'] = trim($data['first_name'] . ' ' . $data['last_name']);
        }

        if (empty($data['name']) && !empty($data['first_name'])) {
            $data['name'] = $data['first_name'];
        }

        if (empty($data['name'])) {
            $data['name'] = 'Facebook Lead ' . $lead['id'];
        }

        return $data;
    }


    private function getFormFields($formId)
    {
        $defaultFields = [
            'status' => LEAD_STATUS_NEW,
            'source' => LEAD_SOURCE_FACEBOOK,
            'projects' => null,
            'interested_in' => null,
            'facing_preference' => null,
            'budget' => null,
            'call_time' => null,
            'lead_value' => null,
            'assigned' => 0,
            'company' => null,
            'title' => null,
            'firm' => null
        ];

        $formFields = $defaultFields;

        // Apply form-specific mapping
        if (isset($this->formMapping[$formId])) {
            $formFields = array_merge($defaultFields, $this->formMapping[$formId]);
        }

        // Apply wildcard mapping
        if (isset($this->formMapping['*'])) {
            $formFields = array_merge($formFields, $this->formMapping['*']);
        }

        // Special handling for form 1265188688846520 - Round-robin between 31 and 32
        if ($formId === '1265188688846520') {
            // Override the assigned field with round-robin result
            $formFields['assigned'] = RoundRobinAssigner::getNextUser($formId);
        }

        return $formFields;
    }

    private function insertIntoCRM($leadData)
    {
        $crmData = [
            'hash' => $leadData['hash'] ?? md5(uniqid()),
            'name' => $leadData['name'] ?? '',
            'title' => $leadData['title'] ?? null,
            'company' => $leadData['company'] ?? null,
            'firm' => $leadData['firm'] ?? null,
            'description' => $leadData['description'] ?? null,
            'country' => $leadData['country'] ?? 0,
            'zip' => $leadData['zip'] ?? null,
            'city' => $leadData['city'] ?? null,
            'state' => $leadData['state'] ?? null,
            'address' => $leadData['address'] ?? null,
            'assigned' => $leadData['assigned'] ?? 0,
            'dateadded' => $leadData['dateadded'] ?? date('Y-m-d H:i:s'),
            'status' => 16,
            'source' => $leadData['source'] ?? LEAD_SOURCE_FACEBOOK,
            'lastcontact' => null,
            'dateassigned' => null,
            'last_status_change' => null,
            'addedfrom' => 1,
            'email' => $leadData['email'] ?? null,
            'website' => $leadData['website'] ?? null,
            'leadorder' => 1,
            'phonenumber' => $leadData['phonenumber'] ?? null,
            'date_converted' => null,
            'lost' => 0,
            'junk' => 0,
            'last_lead_status' => 0,
            'is_imported_from_email_integration' => 0,
            'email_integration_uid' => null,
            'is_public' => 0,
            'default_language' => null,
            'client_id' => 0,
            'converted_by_lead_manager' => 0,
            'lead_value' => $leadData['lead_value'] ?? null,
            'vat' => null,
            'from_ma_form_id' => 0,
            'ma_point' => 0,
            'ma_unsubscribed' => 0,
            'lm_follow_up' => 0,
            'interested_in' => $leadData['interested_in'] ?? null,
            'facing_preference' => $leadData['facing_preference'] ?? null,
            'projects' => $leadData['projects'] ?? null,
            'alt_phonenumber' => $leadData['alt_phonenumber'] ?? null,
            'broker' => null,
            'contact_details' => null,
            'duplicate' => 0,
            'budget' => $leadData['budget'] ?? null,
            'call_time' => $leadData['call_time'] ?? null
        ];

        $crmData = array_filter($crmData, function ($value) {
            return $value !== null && $value !== '';
        });

        return $this->db->insert('tblleads', $crmData);
    }

    public function getStats()
    {
        return [
            'processed' => $this->processedCount,
            'duplicates' => $this->duplicateCount,
            'failed' => $this->failedCount,
            'errors' => $this->errors,
            'assignment_stats' => $this->assignmentStats,
            'tasks_created' => $this->taskCreatedCount,
            'tasks_failed' => $this->taskFailedCount
        ];
    }

    private function logMessage($message)
    {
        error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, LOG_FILE);
    }
}

// ============================================
// MAIN FETCHER CLASS
// ============================================

class FacebookLeadFetcher
{
    private $db;
    private $fbAPI;
    private $leadProcessor;
    private $pageId;
    private $maxLeads;
    private $totalFetched = 0;
    private $errors = [];
    private $formStats = [];
    private $dateFilterInfo;

    public function __construct($pageId = null, $maxLeads = null)
    {
        $this->pageId = $pageId ?: FB_PAGE_ID;
        $this->maxLeads = $maxLeads ?: MAX_LEADS_PER_FETCH;

        $this->db = new Database();
        $this->fbAPI = new FacebookLeadAPI();
        $this->leadProcessor = new LeadProcessor($this->db);
        $this->dateFilterInfo = $this->fbAPI->getDateFilterInfo();
    }

    public function fetchAndProcessLeads()
    {
        $startTime = microtime(true);

        try {
            if (!$this->db->isConnected()) {
                throw new Exception("Database connection failed");
            }

            $this->logMessage("=== Facebook Leads Fetcher started for page: {$this->pageId} ===");

            // Log date filter info
            $filterInfo = $this->dateFilterInfo;
            if ($filterInfo['start'] && $filterInfo['end']) {
                $this->logMessage("Date Filter: {$filterInfo['start']} to {$filterInfo['end']}");
            } else {
                $this->logMessage("Date Filter: No filter (fetching all leads)");
            }

            // Get form IDs to process
            $formIds = $this->getFormIdsToProcess();

            if (empty($formIds)) {
                $this->logMessage("No forms found to process.");
                return [
                    'success' => true,
                    'message' => 'No forms found to process',
                    'fetched' => 0,
                    'processed' => 0,
                    'failed' => 0,
                    'forms_processed' => 0
                ];
            }

            $this->logMessage("Processing " . count($formIds) . " form(s)");

            foreach ($formIds as $formId) {
                $this->processForm($formId);
            }

            // Save last run time for LAST_RUN mode
            $this->fbAPI->saveLastRunTime();

            $executionTime = microtime(true) - $startTime;
            $stats = $this->leadProcessor->getStats();

            $this->logMessage("=== Fetch completed successfully! ===");
            $this->logMessage("Fetched: {$stats['processed']}, Duplicates: {$stats['duplicates']}, Failed: {$stats['failed']}");
            $this->logMessage("Tasks Created: {$stats['tasks_created']}, Tasks Failed: {$stats['tasks_failed']}");
            $this->logMessage("Execution time: " . round($executionTime, 2) . " seconds");

            return [
                'success' => true,
                'fetched' => $this->totalFetched,
                'processed' => $stats['processed'],
                'duplicates' => $stats['duplicates'],
                'failed' => $stats['failed'],
                'errors' => $stats['errors'],
                'execution_time' => $executionTime,
                'forms_processed' => count($formIds),
                'form_stats' => $this->formStats,
                'date_filter' => $this->dateFilterInfo,
                'assignment_stats' => $stats['assignment_stats'],
                'tasks_created' => $stats['tasks_created'],
                'tasks_failed' => $stats['tasks_failed']
            ];
        } catch (Exception $e) {
            $this->logMessage("Fatal Error: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fetched' => $this->totalFetched
            ];
        }
    }

    private function processForm($formId)
    {
        $this->logMessage("Processing form: {$formId}");

        $result = $this->fbAPI->getLeads($formId, $this->maxLeads);

        if ($result === false) {
            $this->logMessage("Failed to fetch leads for form {$formId}: " . $this->fbAPI->getLastError());
            $this->formStats[] = [
                'form_id' => $formId,
                'status' => 'failed',
                'error' => $this->fbAPI->getLastError()
            ];
            return;
        }

        $leads = $result['data'] ?? [];
        $this->totalFetched += count($leads);

        $this->logMessage("Found " . count($leads) . " leads for form {$formId}");

        if (empty($leads)) {
            $this->formStats[] = [
                'form_id' => $formId,
                'status' => 'empty',
                'leads_found' => 0
            ];
            return;
        }

        foreach ($leads as $lead) {
            $this->leadProcessor->processLead($lead);
        }

        $stats = $this->leadProcessor->getStats();

        $this->formStats[] = [
            'form_id' => $formId,
            'status' => 'processed',
            'leads_found' => count($leads),
            'new_leads' => $stats['processed'],
            'duplicates' => $stats['duplicates'],
            'failed' => $stats['failed']
        ];
    }

    private function getFormIdsToProcess()
    {
        if (!FETCH_ALL_FORMS && !empty(ALLOWED_FORM_IDS)) {
            return ALLOWED_FORM_IDS;
        }

        $forms = $this->fbAPI->getForms($this->pageId);

        if ($forms === false || empty($forms['data'])) {
            $this->logMessage("No forms found or API error");
            if ($forms === false) {
                $this->logMessage("API Error: " . $this->fbAPI->getLastError());
            }
            return [];
        }

        $this->logMessage("Found " . count($forms['data']) . " forms on the page");

        $allFormIds = array_column($forms['data'], 'id');

        if (!empty(ALLOWED_FORM_IDS)) {
            return array_intersect($allFormIds, ALLOWED_FORM_IDS);
        }

        return $allFormIds;
    }

    private function logMessage($message)
    {
        error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, LOG_FILE);
    }
}

// ============================================
// SCRIPT EXECUTION
// ============================================

echo "=== Facebook Lead Fetcher for Prefex CRM ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

// Show date filter configuration
echo "=== Date Filter Configuration ===\n";
echo "Filter Type: " . DATE_FILTER_TYPE . "\n";

$dateRange = getDateRangeDisplay();
if ($dateRange) {
    echo "Date Range: " . $dateRange . "\n";
}

echo "\n=== Assignment Configuration ===\n";
echo "Form 1106957472018671 → Assigned to User 28\n";
echo "Form 1287265923224905 → Assigned to User 30\n";
echo "Form 1265188688846520 → Round-robin between Users 31 & 32\n";

if (CREATE_TASK_FOR_LEADS) {
    echo "\n=== Task Creation ===\n";
    echo "✅ Task creation is ENABLED\n";
    echo "   Priority: " . TASK_PRIORITY . " (1=Low, 2=Medium, 3=High, 4=Urgent)\n";
    echo "   Status: " . TASK_STATUS . " (1=Not Started, 2=In Progress, 3=Completed, 4=Waiting for feedback)\n";
} else {
    echo "\n=== Task Creation ===\n";
    echo "❌ Task creation is DISABLED\n";
}

// Check if token is set
if (FB_LONG_LIVED_USER_TOKEN === 'PASTE_YOUR_LONG_LIVED_USER_TOKEN_HERE') {
    echo "\n❌ ERROR: Please update FB_LONG_LIVED_USER_TOKEN in the script with your Long-Lived User Token.\n";
    exit(1);
}

try {
    $fetcher = new FacebookLeadFetcher();
    $result = $fetcher->fetchAndProcessLeads();

    echo "\n=== Results ===\n";
    echo "✅ Forms Processed: " . ($result['forms_processed'] ?? 0) . "\n";
    echo "📊 Leads Fetched: " . ($result['fetched'] ?? 0) . "\n";
    echo "✅ New Leads Added: " . ($result['processed'] ?? 0) . "\n";
    echo "🔄 Duplicates Skipped: " . ($result['duplicates'] ?? 0) . "\n";
    echo "❌ Failed: " . ($result['failed'] ?? 0) . "\n";

    if (isset($result['execution_time'])) {
        echo "⏱️  Execution Time: " . round($result['execution_time'], 2) . " seconds\n";
    }

    // Display task creation statistics
    if (CREATE_TASK_FOR_LEADS) {
        echo "\n=== Task Creation Results ===\n";
        echo "✅ Tasks Created: " . ($result['tasks_created'] ?? 0) . "\n";
        echo "❌ Tasks Failed: " . ($result['tasks_failed'] ?? 0) . "\n";
    }

    if (!empty($result['date_filter']) && $result['date_filter']['start'] && $result['date_filter']['end']) {
        $df = $result['date_filter'];
        echo "\n📅 Date Filter Applied:\n";
        echo "   From: " . $df['start'] . "\n";
        echo "   To: " . $df['end'] . "\n";
    }

    // Display assignment statistics
    if (!empty($result['assignment_stats'])) {
        echo "\n=== Assignment Statistics ===\n";
        $stats = $result['assignment_stats'];
        echo "👤 User 28: " . $stats['user_28'] . " leads\n";
        echo "👤 User 30: " . $stats['user_30'] . " leads\n";
        echo "👤 User 31: " . $stats['user_31'] . " leads\n";
        echo "👤 User 32: " . $stats['user_32'] . " leads\n";
        echo "📋 Unassigned: " . $stats['unassigned'] . " leads\n";

        $totalAssigned = $stats['user_28'] + $stats['user_30'] + $stats['user_31'] + $stats['user_32'];
        if ($totalAssigned > 0) {
            echo "\nDistribution:\n";
            echo "  User 28: " . round(($stats['user_28'] / $totalAssigned) * 100, 1) . "%\n";
            echo "  User 30: " . round(($stats['user_30'] / $totalAssigned) * 100, 1) . "%\n";
            echo "  User 31: " . round(($stats['user_31'] / $totalAssigned) * 100, 1) . "%\n";
            echo "  User 32: " . round(($stats['user_32'] / $totalAssigned) * 100, 1) . "%\n";
        }
    }

    if (!empty($result['form_stats'])) {
        echo "\n=== Form Statistics ===\n";
        foreach ($result['form_stats'] as $stat) {
            echo "Form ID: " . $stat['form_id'] . "\n";
            echo "  Status: " . $stat['status'] . "\n";
            if (isset($stat['error'])) {
                echo "  Error: " . $stat['error'] . "\n";
            }
            echo "  Leads: " . ($stat['leads_found'] ?? 0) . "\n";
            if (isset($stat['new_leads'])) {
                echo "  New: " . $stat['new_leads'] . "\n";
            }
        }
    }

    if (!empty($result['errors'])) {
        echo "\n=== Errors ===\n";
        foreach ($result['errors'] as $error) {
            echo "• " . $error . "\n";
        }
    }

    echo "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";
    echo "=== End ===\n";

    exit($result['success'] ? 0 : 1);
} catch (Exception $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Helper function to display date range
 */
function getDateRangeDisplay()
{
    $filterType = DATE_FILTER_TYPE;

    switch ($filterType) {
        case 'LAST_RUN':
            if (file_exists(LAST_RUN_FILE)) {
                $lastRun = file_get_contents(LAST_RUN_FILE);
                return "Since last run: " . trim($lastRun) . " to now";
            }
            return "First run - fetching last 24 hours";
        case 'LAST_24_HOURS':
            return date('Y-m-d H:i:s', strtotime('-24 hours')) . " to " . date('Y-m-d H:i:s');
        case 'LAST_7_DAYS':
            return date('Y-m-d H:i:s', strtotime('-7 days')) . " to " . date('Y-m-d H:i:s');
        case 'LAST_30_DAYS':
            return date('Y-m-d H:i:s', strtotime('-30 days')) . " to " . date('Y-m-d H:i:s');
        case 'TODAY':
            return date('Y-m-d 00:00:00') . " to " . date('Y-m-d 23:59:59');
        case 'YESTERDAY':
            return date('Y-m-d 00:00:00', strtotime('-1 day')) . " to " . date('Y-m-d 23:59:59', strtotime('-1 day'));
        case 'CUSTOM':
            return CUSTOM_START_DATE . " to " . CUSTOM_END_DATE;
        case 'ALL':
            return "All leads (no date filter)";
        default:
            return null;
    }
}
