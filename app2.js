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

    // Draw video frame onto canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Get image data from canvas
    var imageData = context.getImageData(0, 0, canvas.width, canvas.height);

    // Use jsQR library to decode QR code
    var code = jsQR(imageData.data, imageData.width, imageData.height);

    if (code) {
        // Extract the ID from the decoded text
        var mainIdMatch = code.data.match(/Main ID: (\d+)/);
        if (mainIdMatch) {
            var mainId = parseInt(mainIdMatch[1]); // Convert the ID to an integer

            // Send the mainId back to the parent window
            window.opener.postMessage({ mainId: mainId }, "*");
            //window.close();
        } else {
            output.innerHTML = 'Invalid QR Code format';
        }
    } else {
        output.innerHTML = 'No QR Code detected';
    }
}

// Call captureImage function every second
setInterval(captureImage, 1000);