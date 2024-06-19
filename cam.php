<!DOCTYPE html>
<html>
<head>
    <style>
            .textt{
                display: block;
            }
    </style>
    <title>QR Code Reader</title>
</head>
<body>
    <video id="video" width="450" height="400" autoplay></video>
    <canvas id="canvas" style="display:none;"></canvas>
    <input class="textt"type="text" id="qrResult" placeholder="Scanned QR Code" readonly>
    <div id="output"></div>
    
    <script src="https://rawgit.com/cozmo/jsQR/master/dist/jsQR.js"></script>
    <script>
        // Get access to the camera
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function(stream) {
                var video = document.getElementById('video');
                video.srcObject = stream;
                video.play();
            })
            .catch(function(err) {
                console.log("An error occurred: " + err);
            });

        // Function to capture image from video stream and decode QR code
        function captureImage() {
            var video = document.getElementById('video');
            var canvas = document.getElementById('canvas');
            var context = canvas.getContext('2d');
            var output = document.getElementById('output');
            var qrResult = document.getElementById('qrResult');

            // Draw video frame onto canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Get image data from canvas
            var imageData = context.getImageData(0, 0, canvas.width, canvas.height);

            // Use jsQR library to decode QR code
            var code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code) {
                // Display decoded text in the input field
                qrResult.value = code.data;

                // Send decoded QR code back to parent window 
                window.opener.postMessage(code.data, "*");
                 window.close();
            } else {
                
                output.innerHTML = 'No QR Code detected';
            }
        }

        // Call captureImage function every second
        setInterval(captureImage, 1000);
    </script>
</body>
</html>
