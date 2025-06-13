<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Contact - Baobab";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>

    <?php include('../includes/header.php'); ?>

    <section id="page-header">
        <h2>#Contact Us</h2>
        <p>Leave a Message, We love to hear from you!</p>
    </section>

    <section id="form-details">
        <form action="">
            <span>LEAVE A MESSAGE</span>
            <h2>We love to hear from you</h2>
            <input type="text" placeholder="Your Name">
            <input type="text" placeholder="E-mail">
            <input type="text" placeholder="Subject">
            <textarea name="" id="" cols="30" rows="10" placeholder="Your Message"></textarea>
            <button class="normal">Submit</button>
        </form>
        <div id="right">
            <div id="right-top">
                <img src="../assets/images/contact/contact1.jpeg" alt="">
                <div id="newsletter">
                    <h4>Our Newsletters</h4>
                    <p>Get E-main updates about out latest shop and <span>special offers</span></p>
                    <input type="text" placeholder="Your email address">
                    <button class="normal">Sign Up</button>
                </div>
            </div>
            <!-- <div id="right-bottom">
                <img src="../assets/images/contact/contact2.png" alt="">
            </div> -->
        </div>
    </section>

    <section id="contact"> 
        <div id="heading">
            <span>GET IN TOUCH</span>
            <h2>Visit one of our agency locations or contact us today</h2>
        </div>
        <div id="location">
            <div class="con">
                <div id="gif10"></div>
                <h5>(<i class="fa-solid fa-plus"></i>27) 12 345 6789</h5>
            </div>
            <div class="con">
                <div id="gif11"></div>
                <h5>admin@baobab.com</h5>
            </div>
            <div class="con">
                <div id="gif12"></div>
                <h5>10:00 - 18:00, Mon - Sat</h5>
            </div>
        </div>
        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d211826.6772737197!2d18.173230394531256!3d-33.9464818!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1dcc43c2a3453f71%3A0x27d53fbebd297f8b!2sEduvos%20Cape%20Town%20-%20Mowbray%20Campus!5e0!3m2!1sen!2sza!4v1746729877970!5m2!1sen!2sza" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

    <section>
        <div id="messageContainer">
            <div id="messageHeader">
                <h3>Chat with Baobab</h3>
                <!-- <button onclick="closeChat()" style="float: right; background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px;">Close</button> -->
            </div>
            
            <div id="messagesList"></div>
            
            <div class="message-input">
                <input type="text" id="messageText" placeholder="Type a message..." onkeypress="checkEnter(event)">
                <button onclick="sendMessage()" class="normal">Send</button>
            </div>
        </div>
    </section>

    <script>
        function sendMessage() {
            const messageText = document.getElementById('messageText').value.trim();
            
            if (messageText === '') {
                alert('Please enter a message');
                return;
            }

            // Check if user is logged in
            <?php if (!isset($_SESSION['userId'])): ?>
                alert('Please log in to send messages');
                return;
            <?php endif; ?>

            // Send AJAX request
            fetch('../pages/send_support_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: messageText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageText').value = '';
                    
                    // Add message to the chat display
                    const messagesList = document.getElementById('messagesList');
                    const messageDiv = document.createElement('div');
                    messageDiv.innerHTML = `
                        <div style="background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 5px;">
                            <strong>You:</strong> ${messageText}
                            <small style="float: right;">${new Date().toLocaleTimeString()}</small>
                        </div>
                    `;
                    messagesList.appendChild(messageDiv);
                    messagesList.scrollTop = messagesList.scrollHeight;
                    
                    alert('Message sent successfully!');
                } else {
                    alert('Error sending message: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending message');
            });
        }

        function checkEnter(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function closeChat() {
            document.getElementById('messageContainer').style.display = 'none';
        }
    </script>

    <?php include('../includes/footer.php'); ?>

</body>
</html>