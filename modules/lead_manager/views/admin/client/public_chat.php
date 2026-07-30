<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: transparent;
        }

        .chat-container {
            display: none;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            margin-top: 20px;
            background-color: #fff;
            width: 45%;
        }


        .chat-box {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            min-height: 270px;
        }

        .chat-message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
            max-width: 60%;
            word-wrap: break-word;
        }

        .sent {
            background-color: #dcf8c6;
            margin-left: auto;
        }

        .received {
            background-color: #ffffff;
            margin-right: auto;
            border: 1px solid #ccc;
        }

        .chat-form {
            display: none;
            gap: 10px;
        }

        .chat-form input,
        .chat-form button {
            padding: 10px;
            margin-top: 0px;
            width: 100%;
        }

        .chat-form button {
            border: none;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            background-color: #37395c;
            color: #ffffff;
            font-weight: 600;
            white-space: nowrap;
        }

        /*******CHAT******/

        .quick-chat-area {
            display: none;
            position: fixed;
            right: 30px;
            bottom: 115px;
            background-color: white;
            width: 330px;
            z-index: 9999;
            border-radius: 15px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .quick-chat-header {
            background-color: #161841db;
            padding: 15px;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .quick-chat-header h3 {
            margin: 0;
            color: white;
            font-weight: 600;
            text-align: left;
            font-family: system-ui;
            font-size: 18px;
        }

        .quick-chat-header p {
            text-align: left;
            color: white;
            margin-top: 10px;
            font-family: system-ui;
            font-size: 14px;
            margin-bottom: 0;
            line-height: 19px;
        }


        .quick-chat-body {}

        .start-chat-btn {
            padding: 8px;
            background-color: #161841db;
            color: white !important;
            font-size: 13px;
            border: 0;
            width: 100%;
            border-radius: 6px;
            font-weight: 500;
        }

        .quick-chat-footer {
            background-color: white;
            padding: 8px 10px;
            border-top: 2px solid #dfdfdf;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        .chat-box::-webkit-scrollbar {
            width: 6px;
        }

        /* Track */
        .chat-box::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        .chat-box::-webkit-scrollbar-thumb {
            background: #afabab;
        }

        .chat-message.sent {
            position: relative;
            border-radius: 10px 10px 0 10px;
        }

        .chat-message.sent::before {
            content: "";
            position: absolute;
            left: auto;
            right: 0;
            border-right: none;
            border-left: 5px solid transparent;
            border-top: 4px solid #dcf8c6;
            bottom: -4px;
        }

        .chat-message.received {
            background-color: #f5f5f5;
            margin-right: auto;
            position: relative;
            border: 1px solid transparent;
            border-radius: 10px 10px 10px 0;
        }

        .chat-message.received::before {
            content: "";
            position: absolute;
            bottom: -6px;
            border-top: 6px solid rgb(245 245 245);
            left: 0;
            border-right: 7px solid transparent;
        }

        #message-input {
            width: 80%;
            border: 0;
            border-color: transparent !important;
            outline: 0 !important;
            box-shadow: none !important;
            -webkit-box-shadow: inset 0 0 0 30px #fff !important;
        }

        .send-btn {
            width: 20%
        }

        .chatstart-btn {
            position: fixed;
            right: 50px;
            bottom: 30px;
            cursor: pointer
        }

        .chatstart-btn-icon {
            width: 50px;
            height: 50px;
            background-color: #37395c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chatstart-btn i {
            color: white;
            font-size: 20px;
        }

        .chatstart-btn img {
            position: absolute;
            width: 100px;
            left: -33px;
            top: -29px;
        }

        .end-chat {
            width: 100%;
            align-items: center;
            border: 0;
            background-color: #b1b2d1;
            color: #f90000;
            margin-top: 25px;

        }


        .fa-xmark {
            display: none;
        }



        .profile-card-header {
            padding: 20px;
            background-color: #efefef;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .prfl-inf {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-card-body {
            padding: 10px 20px;
        }

        .chat-message {
            font-size: 11px;
        }

        .prfl-inf img {
            border-radius: 10px;
            width: 100px;
        }

        .navbar-default {
            width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }


        .quick-form-area {
            position: relative;
        }

        .quick-form-area:after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            background-color: #ffffff;
            height: 70px;
            top: 0;
            z-index: -1;
        }

        .quick-form-area form {
            padding: 14px;
            background-color: white;
            margin: 0 14px 30px;
            border-radius: 0px;

        }

        .quick-form-area input,
        .quick-form-area textarea {
            width: 100%;
            padding: 13px 10px;
            margin-bottom: 6px;
            border: 1px solid #d7d7d7 !important;
            border-radius: 5px !important;
            box-shadow: none;
            outline: 0 !important;
            font-size: 12px;
        }

        .quick-form-area button {
            width: 100%;
            background-color: #37395c;
            padding: 8px;
            border-color: transparent;
            color: white;
            font-size: 13px;
            font-weight: 600;
            border-radius: 5px;
            outline: 0;
            box-shadow: none;
        }

        .quick-form-area input.error {
            border: 1px solid red !important
        }

        .quick-form-area textarea.error {
            border: 1px solid red !important
        }

        #loading-gif img {
            height: 30px;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="quick-chat-area" id="quickChatArea">
        <div class="quick-chat-header">
            <h3>Welcome ! </h3>
            <p>Please fill the details below and we will get back to you.</p>
        </div>
        <div class="quick-chat-body">
            <div class="quick-form-area">
                <form id="contactForm" onsubmit="submitContactForm(event)">
                    <input required name="customer_name" id="customer_name" type="text" placeholder="* Name">
                    <input required name="customer_email" id="customer_email" type="email" placeholder="* Email">
                    <textarea required name="customer_message" id="customer_message" placeholder="* Message"
                        row="2"></textarea>
                    <button onClick="validateContactForm();" type="submit">Submit</button>
                </form>
            </div>
            <div id="chatBox" class="chat-box hide"></div>
            <div id="loading-gif">
                <img class="hide" src="<?php echo base_url() ?>/modules/lead_manager/assets/msgwaiting.gif"
                    alt="loading">
            </div>
        </div>
        <div class="quick-chat-footer hide">
            <button class="start-chat-btn" id="startChatBtn" onclick="startChat()">Start Chat</button>
            <form id="chatForm" class="chat-form" onsubmit="sendMessage(event)">
                <input type="text" class="form-control" id="messageInput" placeholder="Type your message" required>
                <div class="send-btn">
                    <button id="send"><i class="fa fa-paper-plane"></i></button>
                </div>
            </form>
            <button class="end-chat" id="endChatBtn" onclick="endChat()">Close<i
                    class="fa-regular fa-circle-xmark"></i></button>
        </div>
    </div>

    <div class="chatstart-btn" onclick="toggleQuickChat()">
        <!-- <img src="https://embed.tawk.to/_s/v4/assets/images/attention-grabbers/168-r-br.svg" /> -->
        <img src="<?php echo base_url('modules/' . MODULE_LEAD_MANAGER . '/assets/we_are_here.svg'); ?>" />
        <div class="chatstart-btn-icon">
            <i class="fa-regular fa-comments" id="chatIcon"></i>
            <i class="fa-solid fa-xmark" id="closeIcon"></i>
        </div>
    </div>
    <textarea name="chatMsgs" id="chatMsgs" class="hide" value=""></textarea>
    <input type="hidden" value="" name="threadid" id="threadid">
    <input type="hidden" value="false" name="human" id="human">
    <input type="hidden" value="" name="user_id" id="user_id">
    <input type="hidden" value="1000" name="open_chat_id" id="open_chat_id">
    <script>
        app.options = {
            lm_firebase_api_key: "<?php echo get_option('lm_firebase_api_key') ?>",
            lm_firebase_auth_domain: "<?php echo get_option('lm_firebase_auth_domain') ?>",
            lm_firebase_project_id: "<?php echo get_option('lm_firebase_project_id'); ?>",
            lm_firebase_storage_bucket: "<?php echo get_option('lm_firebase_storage_bucket'); ?>",
            lm_firebase_messaging_sender_id: "<?php echo get_option('lm_firebase_messaging_sender_id'); ?>",
            lm_firebase_app_id: "<?php echo get_option('lm_firebase_app_id'); ?>",
        };
    </script>
    <script type="module" id="lm-public-chat-js" src="<?php echo module_dir_url('lead_manager', 'assets/js/public_chat.js'); ?>"></script>