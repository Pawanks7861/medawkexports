<?php defined('BASEPATH') or exit('No direct script access allowed');
$check = __DIR__;
$str = preg_replace('/\W\w+\s*(\W*)$/', '$1', $check);
$str . '/third_party/twilio-web/src/Twilio/autoload.php';
class Ai_controller extends CI_Controller
{
    protected $pusher;
    protected $pusher_options = array();

    public function __construct()
    {
        parent::__construct();
    }

    function open_ai()
    {
        $newUser = false;
        $json_data = trim(file_get_contents('php://input'));
        $json_arr = json_decode($json_data, true);
        $api_key = get_option("lm_api_key");
        $chat_end_prompt = get_option("lm_asistant_prompt");
        if (is_array($json_arr)) {
            $json_arr = $json_arr;
        } else {
            if (!empty($json_arr)) {
                $json_arr = (array) json_decode($json_arr);
            }
        }
        if ($json_arr['thread_id'] == NULL) {
            $newUser = true;
        }
        if ($json_arr['chat_end']) {
            $prompt = $chat_end_prompt . $json_arr['content'];
        } else {
            $prompt = $json_arr['content'];
        }
        $assist_id = get_option("lm_asistant_id");
        if (!$assist_id) {
            $assist_id = $this->get_assistant_list($api_key);
            update_option('lm_asistant_id', $assist_id);
        }
        if ($newUser) {
            $thread_id = $this->create_thread($api_key);
        } else {
            $thread_id = $json_arr['thread_id'];
        }
        $existing_customer = $json_arr['existing_customer'];
        if ($this->create_message($api_key, $thread_id, $prompt)) {
            $thread_run_id = $this->thread_run($api_key, $assist_id, $thread_id);
            $status = $this->thread_run_details($thread_id, $thread_run_id, $api_key);
            while ($status != 'completed') {
                $status = $this->thread_run_details($thread_id, $thread_run_id, $api_key);
            }
            if ($status == 'completed') {
                $message_id = $this->thread_message_list($api_key, $thread_id);
                if ($message_id) {
                    $contact = $json_arr;
                    if (isset($contact) && !empty($contact) && $json_arr['chat_end']) {
                        $curl = curl_init();
                        curl_setopt_array(
                            $curl,
                            array(
                                CURLOPT_URL => 'https://api.openai.com/v1/threads/' . $thread_id . '/messages/' . $message_id,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'GET',
                                CURLOPT_HTTPHEADER => array(
                                    'Content-Type: application/json',
                                    'Authorization: Bearer ' . $api_key,
                                    'OpenAI-Beta: assistants=v2',
                                    'Cookie: _cfuvid=4Dhhm.izcsup83dJ0MNoBBeEr7wka0PzwfEZPpJ_AJU-1721198279748-0.0.1.1-604800000'
                                ),
                            )
                        );
                        $result = curl_exec($curl);
                        curl_close($curl);
                        $data['response'] = json_decode($result, true);
                    } else {
                        $reply = $this->retrieve_message($api_key, $thread_id, $message_id);
                    }
                    die;
                }
            }
        }
    }

    function create_thread($key)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.openai.com/v1/threads',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                    'OpenAI-Beta: assistants=v2',
                    'Cookie: _cfuvid=4Dhhm.izcsup83dJ0MNoBBeEr7wka0PzwfEZPpJ_AJU-1721198279748-0.0.1.1-604800000'
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        return $response->id;
    }


    function get_assistant_list($key)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.openai.com/v1/assistants?order=desc&limit=20',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                    'OpenAI-Beta: assistants=v2',
                    'Cookie: __cf_bm=ek.5s_YzycyhKSaQg0OPd0_Weco9sa9XKMmK2tDx9Cg-1721198279-1.0.1.1-H3KoC1qbgI66IFpCuukFWeSMuNikaRPe4OcqGZG_cn1Ir7BoaMQKO.9gr2NCp3ZgdQpQ3cxFG8sJrzlcjOtAYA; _cfuvid=4Dhhm.izcsup83dJ0MNoBBeEr7wka0PzwfEZPpJ_AJU-1721198279748-0.0.1.1-604800000'
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        return $response->first_id;
    }

    function thread_run($key, $assist_id, $thread_id)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.openai.com/v1/threads/' . $thread_id . '/runs',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
    "assistant_id": "' . $assist_id . '"
  }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $key,
                    'Content-Type: application/json',
                    'OpenAI-Beta: assistants=v2',
                    'Cookie: _cfuvid=4Dhhm.izcsup83dJ0MNoBBeEr7wka0PzwfEZPpJ_AJU-1721198279748-0.0.1.1-604800000'
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        return $response->id;
    }
    function thread_message_list($key, $thread_id)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.openai.com/v1/threads/' . $thread_id . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                    'OpenAI-Beta: assistants=v2',
                    'Cookie: _cfuvid=4Dhhm.izcsup83dJ0MNoBBeEr7wka0PzwfEZPpJ_AJU-1721198279748-0.0.1.1-604800000'
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        return $response->first_id;
    }

    function retrieve_message($key, $thread_id, $msg_id)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.openai.com/v1/threads/' . $thread_id . '/messages/' . $msg_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                    'OpenAI-Beta: assistants=v2',
                    'Cookie: _cfuvid=4Dhhm.izcsup83dJ0MNoBBeEr7wka0PzwfEZPpJ_AJU-1721198279748-0.0.1.1-604800000'
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
        die;
    }
    function create_message($key, $thread_id, $content)
    {

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.openai.com/v1/threads/' . $thread_id . '/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
      "role": "user",
      "content": "' . $content . '"
    }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                    'OpenAI-Beta: assistants=v2',
                    'Cookie: _cfuvid=4Dhhm.izcsup83dJ0MNoBBeEr7wka0PzwfEZPpJ_AJU-1721198279748-0.0.1.1-604800000'
                ),
            )
        );
        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        if (isset($response->id))
            return $response->id;
        else
            return false;
    }

    function thread_run_details($thread_id, $thread_run_id, $key)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.openai.com/v1/threads/' . $thread_id . '/runs/' . $thread_run_id,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $key,
                    'OpenAI-Beta: assistants=v2'
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        return $response->status;
    }

    function end_chat()
    {
        $json_str = trim(file_get_contents('php://input'));
        parse_str($json_str, $json_arr);
        $api_key = get_option('lm_api_key');
        if (is_array($json_arr)) {
            $json_arr = $json_arr;
        } else {
            if (!empty($json_arr) && !is_array($json_arr)) {
                $json_arr = (array) json_decode($json_arr);
            }
        }
        if (isset($json_arr) && !empty($json_arr) && $json_arr['chat_end']) {
            $prompt = "Extract all name, phone number and email from the following JSON data and return them in an ARRAY format only with keys \"name\" and \"phonenumber\" and \"email\" and please don't add another string with that:\n\nInput JSON:\n" . json_encode($json_arr['content']) . "\n\nOutput ARRAY:";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                "model" => "gpt-4o",
                "messages" => [
                    [
                        'role' => 'assistant',
                        "content" => $prompt
                    ]
                ],
                "max_tokens" => 100
            ]));

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer " . $api_key
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
            $data['response'] = json_decode($result, true);
            $contacts = $data['response']['choices'][0]['message']['content'];
            $contacts = str_replace("```json", "", $contacts);
            $contacts = str_replace("```", "", $contacts);
            $contacts = json_decode($contacts);
            if (isset($contacts) && !empty($contacts)) {
                if (!class_exists('leads_model')) {
                    $this->load->model('leads_model');
                }
                foreach ($contacts as $contact) {
                    if (!is_array($contact)) {
                        $contact = (array) $contact;
                    }
                    $lead_data = array(
                        'name' => isset($contact['name']) ? $contact['name'] : '',
                        'email' => $contact['email'],
                        'dateadded' => date('Y-m-d H:i:s'),
                        'addedfrom' => get_option('lm_ai_lead_added_by'),
                        'status' => get_option('lm_ai_lead_status'),
                        'source' => get_option('lm_ai_lead_source'),
                    );

                    if (isset($contact['phonenumber']) && !empty($contact['phonenumber'])) {
                        $lead_data['phonenumber'] = $contact['phonenumber'];
                    }
                    $this->db->insert(db_prefix() . 'leads', $lead_data);
                    echo $this->db->insert_id();
                    die;
                }
            }
        }
    }
}
