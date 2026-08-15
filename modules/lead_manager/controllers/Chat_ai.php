<?php defined('BASEPATH') or exit('No direct script access allowed');
class Chat_ai extends ClientsController
{


    public function __construct()
    {
        parent::__construct();
        $this->load->model('lead_manager_model');
    }


    public function index()
    {
        show_404();
    }

    public function form($key = '')
    {
        $key = strtok($key, '?');
        if (!$key) {
            show_404();
        }
        $form = $this->lead_manager_model->get_chat_form_key();
        if (!$form) {
            show_404();
        }
        $form_key = $form->form_key;
        if ($key !== $form_key) {
            show_404();
        }
        $styled = $this->input->get('styled');
        $data['title'] = 'Public Chat';
        $data['styled'] = $styled;
        $this->data($data);
        $this->view('admin/client/public_chat');
        $this->disableNavigation();
        $this->disableSubMenu();
        $this->disableFooter();
        $this->layout();

    }

    public function ai_reply()
    {
        $messages_json = file_get_contents('php://input');
        $messages_array = json_decode($messages_json, true);
        $data = json_decode($_POST['msg'], true);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => site_url('lead_manager/ai_controller/open_ai'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(value: $data['chatMsgs'][0]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain',
                'Cookie: sp_session=60c84943af1854cb7addb32eb1113429ec56a901'
            ),
        ));
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            echo json_encode(['status' => 'error', 'message' => curl_error($curl)]);
        } else {
            $responseData = json_decode($response);
            $thread_id = $responseData->thread_id;
            $message = $responseData->content[0]->text->value;
            echo json_encode(['status' => 'success', 'thread_id' => $thread_id, 'message' => $message, 'response' => $response]);
        }
        curl_close($curl);
    }

    function end_chat()
    {
        $newUser = false;
        $json_str = trim(file_get_contents('php://input'));
        parse_str($json_str, $json_arr);
        $api_key = get_option('lm_api_key');
        $chat_end_prompt = get_option('lm_asistant_prompt');
        if (is_array($json_arr)) {
            $json_arr = $json_arr;
        } else {
            if (!empty($json_arr) && !is_array($json_arr)) {
                $json_arr = (array) json_decode($json_arr);
            }
        }

        if ($json_arr['thread_id'] == NULL) {
            $newUser = true;
        }

        $prompt = $json_arr['content'];

        $thread_id = $json_arr['thread_id'];

        $assist_id = get_option('lm_asistant_id');
        if (!$assist_id) {
            $assist_id = $this->get_assistant_list($api_key);
            update_option('lm_asistant_id', $assist_id);
        }
        if ($newUser) {
            $thread_id = $this->create_thread($api_key);
        } else {
            $thread_id = $json_arr['thread_id'];
        }
        if (1) {

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

                        $prompt = "Extract all name, phone number and email from the following JSON data and return them in an ARRAY format only with keys \"name\" and \"phonenumber\" and \"email\" and please don't add another string with that:\n\nInput JSON:\n" . json_encode($prompt) . "\n\nOutput ARRAY:";
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

                        $contacts = $data['response']['choices'][0]['message']['content'];//$data['response']['content'];
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
                            }
                        }


                    } else {

                        $reply = $this->retrieve_message($api_key, $thread_id, $message_id);
                    }

                    die;
                }
            }
        }
    }

    public function check_customer()
    {
        $messages_json = file_get_contents('php://input');
        $messages_array = json_decode($messages_json, true);
        $data = json_decode($_POST['msg'], true);
        $customer_email = $data['checkData'][0]['customer_email'];

        if ($customer_email) {
            $res = $this->lead_manager_model->is_customer($customer_email);
            if ($res)
                echo json_encode($res);
            else
                echo 'NOT FOUND';
        } else
            echo 'NOT FOUND';
    }





}
