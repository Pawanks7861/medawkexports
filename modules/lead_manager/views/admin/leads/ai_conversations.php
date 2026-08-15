<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <div id="frame">
                        <div id="sidepanel">
                            <div id="profile">
                                <div class="wrap-profile">
                                    <img id="profile-img" src="<?php echo staff_profile_image_url($staff->staffid); ?>"
                                        class="online" alt="" />
                                    <p><?php echo $staff->full_name; ?></p>
                                </div>
                            </div>
                            <div class="wrp-contacts">
                                <ul id="contacts"></ul>
                            </div>
                        </div>
                        <div class="content" id="conversation">
                            <div class="clear-conversation">
                                <span id="selected-sender"></span>
                                <button id="delete-conversation-btn">
                                    <?php echo _l('lm_ai_clr_chat'); ?>
                                </button>
                            </div>
                            <div id="chat-messages"></div>
                            <div class="message-input">
                                <textarea type="text" id="message" class="inputmsg auto-adjust-textarea"
                                    placeholder="Write your message here..."></textarea>
                                <button id="send"><i class="fa fa-paper-plane"></i></button>
                                <input type="hidden" value="0" name="open_chat_id" id="open_chat_id">
                                <textarea name="chatMsgs" id="chatMsgs" class="hide" value=""></textarea>
                                <input class="hide" type="text" value="" name="threadid" id="threadid">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script type="module">
    import {
        initializeApp
    } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js";
    import {
        getDatabase,
        ref,
        onChildAdded,
        onValue,
        push,
        remove
    } from "https://www.gstatic.com/firebasejs/9.6.1/firebase-database.js";
    const firebaseConfig = {
        apiKey: "<?php echo get_option('lm_firebase_api_key'); ?>",
        authDomain: "<?php echo get_option('lm_firebase_auth_domain'); ?>",
        projectId: "<?php echo get_option('lm_firebase_project_id'); ?>",
        storageBucket: "<?php echo get_option('lm_firebase_storage_bucket'); ?>",
        messagingSenderId: "<?php echo get_option('lm_firebase_messaging_sender_id'); ?>",
        appId: "<?php echo get_option('lm_firebase_app_id'); ?>"
    };

    const app = initializeApp(firebaseConfig);
    const database = getDatabase(app);
    const messagesRef = ref(database, 'messages');
    const IPAddress = '<?php echo $_SERVER['REMOTE_ADDR']; ?>';
    var open_chat_id = 0;
    let users = [];
    let userNames = {};
    let unread_chat_msg_count = {};
    let selectedUser = null;
    let latestMessageTimestamps = {};    

    onChildAdded(messagesRef, (snapshot) => {
        const newMessage = snapshot.val();
        const senderIPAddress = newMessage.user_id + ' ' + IPAddress;
        latestMessageTimestamps[senderIPAddress] = newMessage.timestamp;

        if (!users.includes(newMessage.chat_id)) {
            users.push(newMessage.chat_id);
            if (newMessage.username && !(newMessage.chat_id in userNames)) {
                userNames[newMessage.chat_id] = newMessage.username;
            }
        }

        if(unread_chat_msg_count[newMessage.chat_id] && selectedUser != newMessage.user_id) {
            unread_chat_msg_count[newMessage.chat_id] = (unread_chat_msg_count[newMessage.chat_id] + 1);
        }
        else {
            unread_chat_msg_count[newMessage.chat_id] = 1;
        }
        
        displayMessage(newMessage);
    });

    onValue(messagesRef, (snapshot) => {
        const allMessages = snapshot.val();
        $('#chat-messages').empty();
        users = [];
        userNames = {};
        for (let id in allMessages) {
            const message = allMessages[id];
            open_chat_id = message.chat_id;

            if (!users.includes(message.chat_id)) {
                users.push(message.chat_id);
                if (message.username && !(message.chat_id in userNames)) {
                    userNames[message.chat_id] = message.username;
                }
            }
            latestMessageTimestamps[message.user_id] = message.timestamp;
        }
        updateSendersList();
        if (selectedUser) {
            displayMessagesForUser(selectedUser);
        }
    });

    function getIPAddress(senderId) {
        return senderId.split(' ')[0];
    }

    function sanitizeUser(user) {
        return user.replace(/[.\s]+/g, '_');
    }

    function updateSendersList() {
        let tot_unread_chat_msg_count = 0;
        let ipAdd = '<?php echo $_SERVER['REMOTE_ADDR']; ?>';
        $('#contacts').empty();
        users.sort((a, b) => latestMessageTimestamps[b] - latestMessageTimestamps[a]);
        users.forEach(user => {
            if (user !== undefined) {                                              
                const isSelected = user === selectedUser;
                if (!$('#contacts li').filter(function() {                        
                        return $(this).text() === user;
                    }).length) {
                    let userElement = `
                <li class="${isSelected ? 'selected' : ''}" onclick="selectUser('${user}')">
                    <img src="<?php echo base_url() ?>modules/lead_manager/assets/user.png" 
                        alt="User"> 
                    <span>${userNames[user] || 'Unknown User'}</span>`;
                    if(selectedUser !== user)
                        userElement += `<span id="unread_chat_msg_count">(${unread_chat_msg_count[user]})</span>`;
                    else
                        userElement += `<span id="unread_chat_msg_count">(0)</span>`;
                    userElement += `</li>`;
                    $('#contacts').append(userElement);
                    tot_unread_chat_msg_count = tot_unread_chat_msg_count + unread_chat_msg_count[user];
                    $('#tot_unread_chat_msg_count').remove();
                    $('.sub-menu-item-lead_manager_ai_chats a').append('<span id="tot_unread_chat_msg_count">('+tot_unread_chat_msg_count+')</span>');
                }
            }
        });
        const deleteConversationButton = document.getElementById('delete-conversation-btn');
        if (users.length > 0) {
            deleteConversationButton.style.display = 'block';
        } else {
            deleteConversationButton.style.display = 'none';
        }
    }

    function displayMessagesForUser(user) {
        let User = user;
        let chat_id = user;        
        onValue(messagesRef, (snapshot) => {
            const allMessages = snapshot.val();            
            var msgs = [];
            var threadid;
            var chat_id;
            $('#chat-messages').empty();
            for (let id in allMessages) {                
                if (allMessages[id].chat_id == User) {                    
                    displayMessage(allMessages[id]);
                    msgs.push(allMessages[id]['content']);
                    threadid = allMessages[id]['thread_id'];
                    chat_id = allMessages[id]['chat_id'];
                }
            }
            $("#chatMsgs").val(msgs);
            $("#threadid").val(threadid);
            $("#open_chat_id").val(chat_id);
            $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
        });
    }

    window.selectUser = function(user) {
        unread_chat_msg_count[user] = 0;
        selectedUser = user;
        mark_all_messages_as_read(user);
        updateSendersList();
        displayMessagesForUser(user);
        const senderElement = document.getElementById('selected-sender');
        senderElement.innerHTML = `
        <img src="<?php echo base_url() ?>modules/lead_manager/assets/user.png" 
              alt="User"> 
             <span> ${userNames[user] || 'Unknown User'}</span>`;
        const deleteConversationButton = document.getElementById('delete-conversation-btn');
        deleteConversationButton.onclick = function() {
            deleteConversation(selectedUser);
        };
    };

    function displayMessage(message) {
        const messageContainer = document.createElement('div');
        let botSymbol = '';
        if (message.senderId === '<?php echo $_SERVER['REMOTE_ADDR']; ?>' + message.chat_id) {
            messageContainer.classList.add('sent');
        } else if (message.user === 'ai') {
            messageContainer.classList.add('sent');
            botSymbol = '<span style="margin-right: 5px;"><img src="<?php echo base_url() ?>modules/lead_manager/assets/boticon.png" style="width: 30px; height: 40px;" alt="Bot"></span>';
        } else {
            messageContainer.classList.add(message.senderId === '<?php echo $_SERVER['REMOTE_ADDR']; ?>' ? 'sent' : 'received');
        }
        const messageBubble = document.createElement('div');
        messageBubble.classList.add('message');
        messageBubble.innerText = message.text;
        const timeElement = document.createElement('small');
        timeElement.classList.add('message-time');
        timeElement.innerText = new Date(message.timestamp).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        messageContainer.appendChild(messageBubble);
        messageContainer.appendChild(timeElement);

        if (message.user === 'ai') {
            const botElement = document.createElement('div');
            botElement.innerHTML = botSymbol;
            botElement.classList.add('bot-symbol');
            messageContainer.appendChild(botElement);
        }

        document.getElementById('chat-messages').appendChild(messageContainer);
        document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
    }

    function sendMessage() {
        const threadid = $("#threadid").val();
        const senderId = '<?php echo $_SERVER['REMOTE_ADDR']; ?>';
        const senderName = '<?php echo $staff->full_name; ?>';
        const chat_id = $('#open_chat_id').val();
        const message = $('#message').val();
        const staffId = '<?php echo get_staff_user_id(); ?>';
        if (message.trim() !== '') {
            push(messagesRef, {
                chat_id: chat_id,
                receiverId: 'staff',
                senderId: senderId + chat_id,
                user_id: senderId +'.'+ chat_id,
                senderName: senderName,
                text: message,
                staffId: staffId,
                timestamp: Date.now(),
                user: 'public',
                content: message,
                ip_address: senderId + '-' + chat_id,
                read:1
            }).then(() => {
                $('#message').val('');
            }).catch((error) => {
                console.error('Error sending message:', error);
            });
        }
    }

    $('#send').click(function() {
        sendMessage();
    });

    $('#message').keypress(function(e) {
        if (e.which === 13) {
            sendMessage();
            return false;
        }
    });

    function deleteConversation(user) {
        const deleteConversationButton = document.getElementById('delete-conversation-btn');
        onValue(messagesRef, (snapshot) => {
            const allMessages = snapshot.val();
            for (let id in allMessages) {
                if (allMessages[id].chat_id === user) {
                    const messageRef = ref(database, 'messages/' + id);
                    remove(messageRef)
                        .then(() => {
                            console.log('Message deleted:', id);
                        })
                        .catch((error) => {
                            console.error('Error deleting message:', error);
                        });
                }
            }
            const userIndex = users.indexOf(User);
            if (userIndex !== -1) {
                users.splice(userIndex, 1);
            }
            updateSendersList();
            if (users.length > 0) {
                const previousUser = users[0];
                selectUser(previousUser);
            } else {
                $('#chat-messages').empty();
                const senderElement = document.getElementById('selected-sender');
                senderElement.innerHTML = 'No active conversations';
                deleteConversationButton.style.display = 'none';
            }
        });
    }
</script>