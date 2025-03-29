<?php
// Connect to Database
$conn = new mysqli("localhost", "root", "", "umalay");

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Coffee Data (Now includes `id` and `quantity`)
$sql = "SELECT coffeeID, coffee_name, price, quantity FROM coffeeshop;";
$result = $conn->query($sql);

// Check for SQL errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Store results in an array
$coffees = [];
while ($row = $result->fetch_assoc()) {
    $coffees[] = $row;
}

// Close Connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Dashboard - Coffee Shop</title>
  <link rel="stylesheet" href="/assets/css/user.css">
</head>
<body>  
  <header class="navbar navbar-dark bg-primary px-3">
    <a class="navbar-brand" href="#">Coffee Shop</a>
    <a href="logout.php" class="btn btn-light">Logout</a>
  </header>
  <main class="container py-4">
    <h2 class="text-center">Welcome, User!</h2>
    
    <div class="row">
      <!-- Coffee Catalog -->
      <div class="col-md-8">
        <h3>Available Coffees</h3>
        <div class="row" id="coffee-list">
          <?php foreach ($coffees as $coffee): ?>
            <div class="col-md-4">
              <div class="card p-3">
                <h5><?php echo htmlspecialchars($coffee['coffee_name']); ?></h5>
                <p>Price: $<?php echo number_format($coffee['price'], 2); ?></p>
                <p>Stock: <?php echo (int)$coffee['quantity']; ?></p>
                <button class="btn btn-primary" onclick="addToCart(<?php echo (int)$coffee['coffeeID']; ?>, '<?php echo addslashes($coffee['coffee_name']); ?>', <?php echo (float)$coffee['price']; ?>, <?php echo (int)$coffee['quantity']; ?>)">Add to Cart</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- Cart Summary -->
      <div class="col-md-4">
        <h3>Cart</h3>
        <ul id="cart-list" class="list-group">
          <li class="list-group-item">Your cart is empty</li>
        </ul>
        <button class="btn btn-success w-100 mt-3" id="checkout">Checkout</button>
      </div>
    </div>
  </main>
  
  <script>
    let cart = [];

    function addToCart(id, name, price, stock) {
      let quantity = parseInt(prompt(`Enter quantity for ${name} (Stock: ${stock}):`, "1"));
      if (!quantity || quantity <= 0 || quantity > stock) {
        alert("Invalid quantity or not enough stock!");
        return;
      }

      let existingItem = cart.find(item => item.id === id);
      if (existingItem) {
        if (existingItem.quantity + quantity > stock) {
          alert("Not enough stock available!");
          return;
        }
        existingItem.quantity += quantity;
      } else {
        cart.push({ id, name, price, quantity });
      }
      updateCart();
    }

    function updateCart() {
      const cartList = document.getElementById("cart-list");
      cartList.innerHTML = "";
      cart.forEach((item, index) => {
        cartList.innerHTML += `
          <li class="list-group-item d-flex justify-content-between">
            ${item.name} - ${item.quantity} x $${item.price} 
            <button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">X</button>
          </li>`;
      });
      if (cart.length === 0) cartList.innerHTML = "<li class='list-group-item'>Your cart is empty</li>";
    }

    function removeFromCart(index) {
      cart.splice(index, 1);
      updateCart();
    }

    document.getElementById("checkout").addEventListener("click", function() {
      if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
      }

      fetch("/controller/checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart })
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message);
        if (data.status === "success") {
          cart.length = 0;
          updateCart();
          location.reload(); // Refresh to show updated stock
        }
      })
      .catch(error => console.error("Error:", error));
    });
  </script>
</body>
</html>
