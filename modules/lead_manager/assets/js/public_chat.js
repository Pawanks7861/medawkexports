import {
    initializeApp
} from "https://www.gstatic.com/firebasejs/9.6.1/firebase-app.js";
import {
    getDatabase,
    ref,
    onChildAdded,
    onValue,
    push,
    serverTimestamp
} from "https://www.gstatic.com/firebasejs/9.6.1/firebase-database.js";

const firebaseConfig = {
    apiKey: app.options.lm_firebase_api_key,
    authDomain: app.options.lm_firebase_auth_domain,
    projectId: app.options.lm_firebase_project_id,
    storageBucket: app.options.lm_firebase_storage_bucket,
    messagingSenderId: app.options.lm_firebase_messaging_sender_id,
    appId: app.options.lm_firebase_app_id
};
const firebaseApp = initializeApp(firebaseConfig);
const database = getDatabase(firebaseApp);
const messagesRef = ref(database, 'messages');
var open_chat_id = "";
var chat_id = $("#open_chat_id").val();
var human = false;
var existing_customer = false;
const ipAdd = await getIpAddressOfSystem();

onChildAdded(messagesRef, (snapshot) => {
    const messageData = snapshot.val();
    var chat_id = $("#open_chat_id").val();

    if (chat_id == messageData.chat_id) {
        if (messageData.content === 'human=true') {
            displayMessage('Now human will handle the further chat.', 'sent');
        } else {

            if (messageData.user === 'ai' || messageData.user === 'staff') {
                displayMessage(messageData.text, 'received');
            } else {
                if (messageData.user === 'anonymous') {
                    displayMessage(messageData.text, 'sent');
                } else {
                    displayMessage(messageData.text, 'received');
                }
            }
        }
    }
});

var isOpenChatIdSet = false;
var isThreadIdSet = false;

onValue(messagesRef, (snapshot) => {
    const allMessages = snapshot.val();
    var chat_id = $("#open_chat_id").val();
    open_chat_id = chat_id;
    var msgs = [];
    Object.keys(allMessages).forEach(function(key) {
        if (chat_id == allMessages[key]['chat_id']) {
            msgs.push(allMessages[key]['content']);
        }
        if(allMessages[key]['chat_id'] > open_chat_id) {
            open_chat_id = allMessages[key]['chat_id'];
        }
    });

    $("#chatMsgs").val(msgs);

    if (!isOpenChatIdSet) {
        $("#open_chat_id").val(open_chat_id);
        isOpenChatIdSet = true;
    }
    if (human)
        $("#loading-gif img").addClass("hide");
});

window.toggleQuickChat = function toggleQuickChat() {
    const quickChatArea = document.getElementById('quickChatArea');
    const chatIcon = document.getElementById('chatIcon');
    const closeIcon = document.getElementById('closeIcon');

    if (quickChatArea.style.display === 'none' || quickChatArea.style.display === '') {
        quickChatArea.style.display = 'block';
        chatIcon.style.display = 'none';
        closeIcon.style.display = 'block';
    } else {
        quickChatArea.style.display = 'none';
        chatIcon.style.display = 'block';
        closeIcon.style.display = 'none';
    }
}

window.startChat = function startChat() {
    const startChatBtn = document.getElementById('startChatBtn');
    const chatForm = document.getElementById('chatForm');
    if (startChatBtn.textContent === 'Start Chat') {
        startChatBtn.style.display = 'none';
        chatForm.style.display = 'flex';
        $("#chatMsgs").val("");
        $("#threadid").val("");
        var open_chat_id = parseInt($("#open_chat_id").val());
        $("#open_chat_id").val((open_chat_id + 1));
    } else {
        endChat();
    }
}

// window.endChat = function endChat() {
//     toggleQuickChat();
// };
window.endChat = function endChat() {
    const startChatBtn = document.getElementById('startChatBtn');
    const chatBox = document.getElementById('chatBox');
    const chatForm = document.getElementById('chatForm');
    const threadid = document.getElementById('threadid').value;
    const messageInput = document.getElementById('messageInput');
    const chatMsgs = document.getElementById('chatMsgs').value;
    const newMessage = "chat_end=true";
    const updatedChatMsgs = chatMsgs + " " + newMessage;
    const messageData = {
        thread_id: threadid,
        chat_end: true,
        content: updatedChatMsgs
    };
    $.post(site_url + 'lead_manager/ai_controller/end_chat', messageData)
        .done(function(response) {
            console.log(response);
            $("#chatMsgs").val("");
            $("#threadid").val("");
            $("#user_id").val("");
            $("#open_chat_id").val("");
            $(".quick-chat-footer").addClass("hide");
            $("#contactForm").removeClass("hide");
            $(".quick-form-area").removeClass("hide");
            $(".chat-box").addClass("hide");
            $("#customer_name").val('');
            $("#customer_email").val('');
            $("#customer_message").val('');
            existing_customer = false;
        })
        .fail(function(error) {
            console.error('Error:', error);
            $("#chatMsgs").val("");
            $("#threadid").val("");
            $("#user_id").val("");
            $("#open_chat_id").val("");
            $(".quick-chat-footer").addClass("hide");
            $("#contactForm").removeClass("hide");
            $(".quick-form-area").removeClass("hide");
            $(".chat-box").addClass("hide");
            $("#customer_name").val('');
            $("#customer_email").val('');
            $("#customer_message").val('');
            existing_customer = false;
        });
    messageInput.value = '';
    startChatBtn.textContent = 'Start Chat';
    chatForm.style.display = 'none';
    chatBox.innerHTML = '';
    messageInput.value = '';
    toggleQuickChat();
};

window.validateContactForm = function validateContactForm(event) {
    var customer_name = $("#customer_name").val();
    var customer_email = $("#customer_email").val();
    var customer_message = $("#customer_message").val();

    $("#customer_name").removeClass("error");
    $("#customer_email").removeClass("error");
    $("#customer_message").removeClass("error");

    if (customer_name == "") {
        $("#customer_name").addClass("error");
    }
    if (customer_email == "") {
        $("#customer_email").addClass("error");
    }
    if (!validateEmail(customer_email)) {
        $("#customer_email").addClass("error");
    }
    if (customer_message == "") {
        $("#customer_message").addClass("error");
    }
}

function validateEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(email);
}

window.submitContactForm = function submitContactForm(event) {
    event.preventDefault();
    const csrftoken = csrfData.formatted.csrf_token_name;
    $(".quick-chat-header p").addClass("hide");
    $("#contactForm").addClass("hide");
    $("#chatBox").removeClass("hide");
    $(".quick-chat-footer").removeClass("hide");
    $(".quick-form-area").addClass("hide");
    $("#chatBox").html("");
    $(".start-chat-btn").trigger("click");
    $("#loading-gif img").removeClass("hide");
    var customer_name = $("#customer_name").val();
    var customer_email = $("#customer_email").val();
    var customer_message = $("#customer_message").val();
    const check = {
        customer_name: customer_name,
        customer_email: customer_email,
        customer_message: customer_message
    };
    $.post(site_url + 'lead_manager/chat_ai/check_customer', {
                msg: JSON.stringify({ checkData: [check] }),
                csrf_token_name: csrftoken
            },
            function(response) {
                if (response && response.length > 0 && response != "NOT FOUND") {
                    const threadid = document.getElementById('threadid').value;
                    const messageInput = document.getElementById('messageInput');
                    const message = messageInput.value;
                    const csrftoken = csrfData.formatted.csrf_token_name;
                    const IPAddress = ipAdd;
                    const chat_id = $("#open_chat_id").val();
                    var msgdata = JSON.parse(response);
                    var name = msgdata['name'];
                    var user_id = msgdata['id'];
                    $("#user_id").val(user_id);
                    var website = $("#website").val();
                    var phonenumber = msgdata['phonenumber'];
                    if (name) {
                        const threadid = document.getElementById('threadid').value;
                        var msg = "Hi, My name is " + customer_name + ", my email address is " + customer_email;
                        const csrftoken = csrfData.formatted.csrf_token_name;
                        const IPAddress = ipAdd;
                        const msgData = {
                            text: msg,
                            content: msg,
                            user: 'anonymous',
                            user_id: IPAddress + '.' + user_id,
                            username: customer_name,
                            timestamp: serverTimestamp(),
                            chat_id: chat_id,
                            ip_address: IPAddress + '-' + chat_id,
                            thread_id: threadid,
                            senderName: '',
                            staffId: '',
                            receiverId: 'public',
                            senderId: '',
                            chat_end: false,
                            read:0
                        };
                        push(messagesRef, msgData);
                        existing_customer = true;
                        sendDefaultMessage(msg, true);
                    }
                    return false;
                } else {
                    const threadid = document.getElementById('threadid').value;
                    var msg = "Hi, My name is " + customer_name + " and my email is " + customer_email;
                    const csrftoken = csrfData.formatted.csrf_token_name;
                    const IPAddress = ipAdd;
                    const chat_id = $("#open_chat_id").val();
                    var website = $("#website").val();
                    const msgData = {
                        text: msg,
                        content: msg,
                        user: 'anonymous',
                        user_id: IPAddress + '.' + chat_id,
                        username: customer_name,
                        timestamp: serverTimestamp(),
                        chat_id: chat_id,
                        ip_address: IPAddress + '-' + chat_id,
                        thread_id: threadid,
                        senderName: '',
                        staffId: '',
                        receiverId: 'public',
                        senderId: '',
                        chat_end: false,
                        read:0
                    };
                    push(messagesRef, msgData);
                    existing_customer = true;
                    var msg = "Hi, My name is " + customer_name + " and my email is " + customer_email;
                    sendDefaultMessage(msg, true);
                    return false;
                }

            })
        .fail(function(error) {
            console.error('Error:', error);
        });
}



function isValidJSON(str) {
    str = str.replace("```json ", "");
    str = str.replace("Thank you for chatting with me. Have a wonderful day! ", "");
    str = str.replace("```", "");
    str = str.trim();
    console.log("In isValidJSON: " + str);
    try {
        JSON.parse(str);
        return true;
    } catch (e) {
        return false;
    }
}

window.sendMessage = function sendMessage(event) {
    event.preventDefault();
    const threadid = document.getElementById('threadid').value;
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value;
    const csrftoken = csrfData.formatted.csrf_token_name;
    const IPAddress = ipAdd;
    console.log("ipaddress: " + IPAddress);
    const chat_id = $("#open_chat_id").val();
    var customer_name = $("#customer_name").val();
    var website = $("#website").val();
    $("#loading-gif img").removeClass("hide");
    var uid = $("#user_id").val();
    if (uid == '')
        uid = IPAddress + '.' + chat_id;
    if (message) {
        const messageData = {
            text: message,
            content: message,
            user: 'anonymous',
            user_id: uid,
            username: customer_name,
            timestamp: serverTimestamp(),
            chat_id: chat_id,
            ip_address: IPAddress + '-' + chat_id,
            thread_id: threadid,
            senderName: '',
            staffId: '',
            receiverId: 'public',
            senderId: '',
            chat_end: false,
            existing_customer: existing_customer,
            read:0
        };
        push(messagesRef, messageData);
        if (!human) {
            $.post(site_url + 'lead_manager/chat_ai/ai_reply', {
                        msg: JSON.stringify({ chatMsgs: [messageData] }),
                        csrf_token_name: csrftoken
                    },
                    function(response) {
                        $("#loading-gif img").addClass("hide");
                        if (response && response.length > 0) {
                            var msgdata = JSON.parse(response);
                            var msg = msgdata['message'];
                            var threadid = msgdata['thread_id'];
                            if (msg && msg !== "human=true") {
                                const msgData = {
                                    text: msg,
                                    content: msg,
                                    user: 'ai',
                                    user_id: uid,
                                    username: customer_name,
                                    timestamp: serverTimestamp(),
                                    chat_id: chat_id,
                                    ip_address: IPAddress + '-' + chat_id,
                                    thread_id: threadid,
                                    senderName: '',
                                    staffId: '',
                                    receiverId: 'public',
                                    senderId: '',
                                    chat_end: false,
                                    read:0
                                };
                                push(messagesRef, msgData);
                            } else {
                                displayMessage('Now human will handle the further chat.', messageData.user === 'anonymous' ? 'received' : 'sent');
                                human = true;
                            }
                        }
                        return false;
                    })
                .fail(function(error) {
                    console.error('Error:', error);
                });
        }
        messageInput.value = '';
    }
}



function sendDefaultMessage(message, reply) {
    const threadid = document.getElementById('threadid').value;
    const csrftoken = csrfData.formatted.csrf_token_name;
    const IPAddress = ipAdd;
    const chat_id = $("#open_chat_id").val();
    var customer_name = $("#customer_name").val();
    var website = $("#website").val();
    var uid = $("#user_id").val();
    if (uid == '')
        uid = IPAddress + '.' + chat_id;
    if (message) {
        const messageData = {
            text: message,
            content: message,
            user: 'anonymous',
            user_id: uid,
            username: customer_name,
            timestamp: serverTimestamp(),
            chat_id: chat_id,
            ip_address: IPAddress + '-' + chat_id,
            thread_id: threadid,
            senderName: '',
            staffId: '',
            receiverId: 'public',
            senderId: '',
            chat_end: false,
            existing_customer: existing_customer,
            read:0
        };
        if (!human) {
            $.post(site_url + 'lead_manager/chat_ai/ai_reply', {
                        msg: JSON.stringify({ chatMsgs: [messageData] }),
                        csrf_token_name: csrftoken
                    },
                    function(response) {
                        $("#loading-gif img").addClass("hide");
                        if (response && response.length > 0 && reply) {
                            var msgdata = JSON.parse(response);
                            var msg = msgdata['message'];
                            if (isValidJSON(msgdata['message'])) {
                                console.log("valid jSON");
                                var str = msgdata['message'];
                                str = str.replace("```json ", "");
                                str = str.replace("Thank you for chatting with me. Have a wonderful day! ", "");
                                str = str.replace("```", "");
                                str = str.trim();
                                var content = JSON.parse(str);
                                msg = "Hi " + content['name'] + ", Welcome back. How can I assist you today?";
                            }
                            console.log("INvalid jSON");
                            var threadid = msgdata['thread_id'];
                            $("#threadid").val(threadid);
                            if (msg && msg !== "human=true") {
                                const msgData = {
                                    text: msg,
                                    content: msg,
                                    user: 'ai',
                                    user_id: uid,
                                    username: customer_name,
                                    timestamp: serverTimestamp(),
                                    chat_id: chat_id,
                                    ip_address: IPAddress + '-' + chat_id,
                                    thread_id: threadid,
                                    senderName: '',
                                    staffId: '',
                                    receiverId: 'public',
                                    senderId: '',
                                    chat_end: false,
                                    read:0
                                };
                                push(messagesRef, msgData);
                            } else {
                                displayMessage('Your chat has been transferred to a staff member.', messageData.user === 'anonymous' ? 'received' : 'sent');
                                human = true;
                            }
                        }
                        return false;
                    })
                .fail(function(error) {
                    console.error('Error:', error);
                });
        }
        messageInput.value = '';
    }
}


function displayMessage(text, type) {
    const chatBox = document.getElementById('chatBox');
    const messageElement = document.createElement('div');
    messageElement.classList.add('chat-message', type);
    messageElement.textContent = text;
    chatBox.appendChild(messageElement);
    chatBox.scrollTop = chatBox.scrollHeight;
}
$('#customer_name').keypress(function(e) {

    $('#customer_name').removeClass('error');

});
$('#customer_email').keypress(function(e) {

    $('#customer_email').removeClass('error');

});
$('#customer_message').keypress(function(e) {
    $('#customer_message').removeClass('error');
});

function toggleQuickChat() {
    const quickChatArea = document.getElementById('quickChatArea');
    const chatIcon = document.getElementById('chatIcon');
    const closeIcon = document.getElementById('closeIcon');

    if (quickChatArea.style.display === 'none' || quickChatArea.style.display === '') {
        quickChatArea.style.display = 'block';
        chatIcon.style.display = 'none';
        closeIcon.style.display = 'block';
    } else {
        quickChatArea.style.display = 'none';
        chatIcon.style.display = 'block';
        closeIcon.style.display = 'none';
    }
}

async function getIpAddressOfSystem() {
    try {
        const response = await fetch('https://api64.ipify.org?format=json');
        const data = await response.json();
        return data.ip;
    } catch (error) {
        console.error('Error fetching IP address:', error);
        return null; // Or any other default value in case of error
    }
}