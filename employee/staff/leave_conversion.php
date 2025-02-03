<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave to Money Converter</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100vh;
      background-color: #f4f4f4;
    }

    header, footer {
      width: 100%;
      background-color: black;
      color: white;
      text-align: center;
      padding: 10px 0;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: white;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 50%;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    th, td {
      padding: 12px;
      text-align: left;
      border: 1px solid #ddd;
    }

    th {
      background-color: black;
      color: white;
    }

    input {
      padding: 10px;
      margin: 10px 0;
      width: 100%;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      padding: 10px 20px;
      font-size: 16px;
      background-color: black;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
      width: 100%;
    }

    button:hover {
      background-color: dark;
    }

    .result {
      font-weight: bold;
    }

    .error {
      color: red;
    }

    .success {
      color: green;
    }
  </style>
</head>
<body>
  <header>
    <h1>Leave to Money Converter</h1>
  </header>

  <button id="openModalBtn">Open Converter</button>

  <div id="myModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <table>
        <thead>
          <tr>
            <th colspan="2">Convert Your Leave Days to Money</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Enter number of leave days:</td>
            <td><input type="number" id="leaveDays" required></td>
          </tr>
          <tr>
            <td>Enter your daily rate (in Peso):</td>
            <td><input type="number" id="dailyRate" required></td>
          </tr>
          <tr>
            <td colspan="2" style="text-align: center;">
              <button id="calculateBtn">Calculate</button>
            </td>
          </tr>
          <tr>
            <td colspan="2" style="text-align: center;">
              <p id="resultMessage" class="result"></p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <footer>
    <p>&copy; 2023 Leave to Money Converter</p>
  </footer>

  <script>
    // Get elements
    const openModalBtn = document.getElementById('openModalBtn');
    const modal = document.getElementById('myModal');
    const closeBtn = document.getElementsByClassName('close')[0];
    const calculateBtn = document.getElementById('calculateBtn');
    const leaveDaysInput = document.getElementById('leaveDays');
    const dailyRateInput = document.getElementById('dailyRate');
    const resultMessage = document.getElementById('resultMessage');

    // Open modal
    openModalBtn.onclick = function() {
      modal.style.display = 'flex';
    }

    // Close modal
    closeBtn.onclick = function() {
      modal.style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }

    // Handle calculation
    calculateBtn.addEventListener('click', () => {
      const leaveDays = parseFloat(leaveDaysInput.value);
      const dailyRate = parseFloat(dailyRateInput.value);

      if (isNaN(leaveDays) || isNaN(dailyRate) || leaveDays <= 0 || dailyRate <= 0) {
        resultMessage.textContent = 'Please enter valid values.';
        resultMessage.classList.add('error');
        resultMessage.classList.remove('success');
      } else {
        const totalAmount = leaveDays * dailyRate;
        resultMessage.textContent = `Your total leave value is ₱${totalAmount.toFixed(2)}.`;
        resultMessage.classList.add('success');
        resultMessage.classList.remove('error');
      }
    });
  </script>

</body>
</html>