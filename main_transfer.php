<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <div id="promptMessage" class="prompt-message"></div>
  <title>Mass Transfer</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Center content within the .transferr div */
    .transferr {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      text-align: center;
      border: 1px solid #ccc;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      background-color: gainsboro;
      flex-direction: row;
    }

    button, b {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: white;
    background-color: darkcyan;
}

    
    form {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #rackValContainer {
      display: none;
    }

    /* Table styles */
    .table-container {
      overflow-x: auto;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
    }

    .table th, .table td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .table th {
      background-color: #f2f2f2;
    }

    .table thead tr {
  position: sticky;
  top: 0;
  background-color: #fff; /* Same background color as the table */
}


/* Prompt message style */
.prompt-message {
    background-color: lightgreen;
    top: inherit;
    padding: 10px;
    border: 1px solid green;
    color: darkgreen;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    display: none;
    border-radius: 8px;
}


  </style>

 
</head>
<body>
  <h1>Main Inventory</h1>

  <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "qr2";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $rackFilter = isset($_POST['rackFilter']) ? mysqli_real_escape_string($conn, $_POST['rackFilter']) : "";
    $rackValFilter = isset($_POST['rackValFilter']) ? mysqli_real_escape_string($conn, $_POST['rackValFilter']) : "";

    $query = "SELECT * FROM main_inv WHERE 1=1";

    if (!empty($rackFilter)) {
      $query .= " AND racks = '$rackFilter'";
    }

    if (!empty($rackValFilter)) {
      $query .= " AND rack_val = '$rackValFilter'";
    }

    $query .= " ORDER BY source_id ASC";

    $result = mysqli_query($conn, $query);
    if (!$result) {
      die("Query failed: " . mysqli_error($conn));
    }
  ?>

<button type="button" onclick="openQRScanner2()">Scan QR Code</button>
  <div class="transferr"> 

    
  <div class="from" style="margin: 5px;">
    <form method="POST" action="">
      <label for="rackFilter">Process:</label>
      <select id="rackFilter" name="rackFilter" onchange="this.form.submit()">
        <option value="">All</option>
        <?php
          $rackValues = $conn->query("SELECT DISTINCT racks FROM main_inv");
          while ($row = $rackValues->fetch_assoc()) {
            $selected = ($row['racks'] == $rackFilter) ? 'selected' : '';
            echo "<option value='" . $row['racks'] . "' $selected>" . $row['racks'] . "</option>";
          }
        ?>
      </select>

      <label for="rackValFilter">Rack:</label>
      <select id="rackValFilter" name="rackValFilter" onchange="this.form.submit()">
        <option value="">All</option>
        <?php
          $rackValValues = $conn->query("SELECT DISTINCT rack_val FROM main_inv");
          while ($row = $rackValValues->fetch_assoc()) {
            $selected = ($row['rack_val'] == $rackValFilter) ? 'selected' : '';
            echo "<option value='" . $row['rack_val'] . "' $selected>" . $row['rack_val'] . "</option>";
          }
        ?>
      </select>
    </form>
  </div>





  <div class="to">
    <span id="selectedItemsCount">0 Items selected</span>
    <button type="button" onclick="uncheckAll()" style="background: #828d8d;">Uncheck All</button>
    <form id="massUpdateForm" method="post" action="mass_update.php">
      <label for="rackSelect"></label>
      <select name="rack" id="rackSelect" onchange="toggleRackValContainer()" style="margin-left: 70px; margin-top: 10px;">
        <option value="Staging">Staging</option>
        <option value="Lamination">Lamination</option>
        <option value="Layering">Layering</option>
        <option value="Rejects">Rejects</option>
        <option value="Old Storage">Old Storage</option>
        <option value="Others">Others</option>
      </select>
      <div id="rackValContainer" style="display: none;">
        <label for="rackVal" style="margin-top: 9px;">Rack:</label>
        <input type="text" name="rackVal" id="rackVal" style="width: 50px;">
        <button type="button" onclick="openQRScanner()" style="margin-top: 9px;">Scan</button>
        
      </div>
      <button type="button" onclick="submitMassUpdateForm()" style="margin-top: 9px;">Update</button>
    </form>
    </div>
    <!-- <a href="db.php" target="_blank">This is a link</a> -->
  </div>
       
        
  <?php if ($result->num_rows > 0): ?>
    <div class="table-container">
    <table class="table">
      <thead>
        <tr>
          
          <th>Select</th>
          <th>Ref</th>
          <th>Racks</th>
          <th>Main ID</th>
          <th>Actual Count</th>
          <th>Generate QR</th>
          <th>Order Window</th>
          <th>Purchase No</th>
          <th>Serial</th>
          <th>Material Explain</th>
          <th>Color</th>
          <th>Supplier Name</th>
          <th>Pur. Qty</th>
          <th>Prod. No</th>
          <th>Article</th>
          <th>Shoe Name</th>
          <th>Location</th>
          <th>UUID</th>
          
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?> 
          <tr>
            
          <td><input type="checkbox" name="selectedItems[]" value="<?php echo $row['id']; ?>" onclick="saveSelectedItems(); updateSelectedItemsCount();"></td>
          <td><?php echo $row['ref']; ?></td>
          <td><?php echo $row['racks'] . "/" . $row['rack_val']; ?></td>
          <td><?php echo $row['id']; ?></td>
          <td contenteditable="true" onBlur="updateCount2(this, '<?php echo $row['id']; ?>')"><?php echo $row['count']; ?></td>
          <td><a href="../generateqr/generate_qr2.php?id=<?php echo $row['id']; ?>" onclick="openInNewWindow2(this.href); return false;">QR</a></td>
          <td><?php echo $row['order_win']; ?></td>
          <td><?php echo $row['purchase_no']; ?></td>
          <td><?php echo $row['serial_no']; ?></td>
          <td><?php echo $row['mat_ex']; ?></td>
          <td><?php echo $row['color']; ?></td>
          <td><?php echo $row['supp_name']; ?></td>
          <td><?php echo $row['pur_qty']; ?></td>
          <td><?php echo $row['prod_no']; ?></td>
          <td><?php echo $row['article']; ?></td>
          <td><?php echo $row['shoe_name']; ?></td>
          <td><?php echo $row['dev_loc']; ?></td>
          <td><?php echo $row['uuid']; ?></td>
           
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    </div>



  <?php else: ?>
    <p>No data found.</p>
  <?php endif; ?>

  <?php $conn->close(); ?>
  
</body>

<script>

function openInNewWindow2(url) {
    window.open(url, 'newwindow', 'width=800,height=600');
    
}
function openQRScanner2() {
    var scannerWindow = window.open("../function-main/cam2.php", "_blank", "width=500,height=600");
    scannerWindow.onload = function() {
        scannerWindow.postMessage({ action: "scanQR" }, "*");
    };
    window.addEventListener("message", function(event) {
        if (event.source === scannerWindow) {
            var mainId = event.data.mainId;
            var checkbox = document.querySelector(`input[name="selectedItems[]"][value="${mainId}"]`);
            if (checkbox) {
                checkbox.checked = true;
                saveSelectedItems();
                updateSelectedItemsCount();
                showPromptMessage('Checked');
            } else {
                alert(`No item found with Main ID: ${mainId}`);
            }
        }
    });
}

function showPromptMessage(message) {
    var promptMessage = document.getElementById('promptMessage');
    if (!promptMessage) {
        promptMessage = document.createElement('div');
        promptMessage.id = 'promptMessage';
        promptMessage.className = 'prompt-message';
        document.body.appendChild(promptMessage);
    }
    promptMessage.textContent = message;
    promptMessage.style.display = 'block';
    setTimeout(function() {
        promptMessage.style.display = 'none';
    }, 2000);
}



  function submitMassUpdateForm() {
    const selectedItems = document.querySelectorAll('input[name="selectedItems[]"]:checked');
    const selectedItemIds = Array.from(selectedItems).map(item => item.value);

    if (selectedItemIds.length === 0) {
      alert('Please select at least one item to update.');
      return;
    }

    const formData = new FormData(document.getElementById('massUpdateForm'));
    selectedItemIds.forEach(itemId => {
      formData.append('selectedItems[]', itemId);
    });

    fetch('mass_update.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (response.ok) {
        location.reload();
      } else {
        alert('An error occurred while updating the items.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while updating the items.');
    });
  }


function openQRScanner(index) {
    var scannerWindow = window.open("cam.php", "_blank", "width=500,height=600");
    scannerWindow.onload = function() {
      scannerWindow.postMessage({ index: index }, "*");
    };
    window.addEventListener("message", function(event) {
      if (event.source === scannerWindow) {
        var qrCode = event.data;
        var inputField = document.getElementById('rackVal');
       
        inputField.value = qrCode;
        
      }
    });
  }

       function toggleRackValContainer() {
      const rackSelect = document.getElementById('rackSelect');
      const rackValContainer = document.getElementById('rackValContainer');

      if (rackSelect.value === 'Others') {
        rackValContainer.style.display = 'inline-block';
      } else {
        rackValContainer.style.display = 'none';
      }
    }

    function saveSelectedItems() {
      const selectedItems = document.querySelectorAll('input[name="selectedItems[]"]:checked');
      const selectedItemIds = Array.from(selectedItems).map(item => item.value);
      document.cookie = "selectedItemIds=" + selectedItemIds.join(',') + "; path=/";
    }

    function loadSelectedItems() {
      const cookies = document.cookie.split(';');
      const selectedItemIdsCookie = cookies.find(cookie => cookie.trim().startsWith('selectedItemIds='));
      if (selectedItemIdsCookie) {
        const selectedItemIds = selectedItemIdsCookie.split('=')[1].split(',');
        const checkboxes = document.querySelectorAll('input[name="selectedItems[]"]');
        checkboxes.forEach(checkbox => {
          if (selectedItemIds.includes(checkbox.value)) {
            checkbox.checked = true;
          }
        });
      }
    }

    function uncheckAll() {
      const checkboxes = document.querySelectorAll('input[name="selectedItems[]"]');
      checkboxes.forEach(checkbox => checkbox.checked = false);
      document.cookie = "selectedItemIds=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
      updateSelectedItemsCount();
    }

    function updateSelectedItemsCount() {
      const selectedItems = document.querySelectorAll('input[name="selectedItems[]"]:checked');
      const count = selectedItems.length;
      document.getElementById('selectedItemsCount').textContent = count + ' Items selected';
    }

    window.addEventListener('load', () => {
      loadSelectedItems();
      updateSelectedItemsCount();
    });

    
  </script>
</html>
