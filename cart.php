<?php
session_start();
require 'db.php';

// Add product to cart
if (isset($_POST['id'])) {
    $productId = $_POST['id'];

    // Check if the cart already exists in the session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    $productFound = false;
    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['id'] === $productId) {
            $cartItem['quantity']++;
            $productFound = true;
            break;
        }
    }

    // If not found, add a new product to the cart
    if (!$productFound) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if ($product) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        }
    }
}

// Remove product from cart
if (isset($_GET['remove'])) {
    $productIdToRemove = $_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $cartItem) {
        if ($cartItem['id'] == $productIdToRemove) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);  // Reindex the array
}

// Update product quantity in cart
if (isset($_POST['update'])) {
    $productIdToUpdate = $_POST['product_id'];
    $newQuantity = $_POST['quantity'];

    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['id'] == $productIdToUpdate) {
            $cartItem['quantity'] = $newQuantity;
            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h2>Your Cart</h2>

    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php foreach ($_SESSION['cart'] as $cartItem): ?>
                    <tr>
                        <td><?php echo $cartItem['name']; ?></td>
                        <td>$<?php echo number_format($cartItem['price'], 2); ?></td>
                        <td>
                            <form action="cart.php" method="post" class="d-inline">
                                <input type="number" name="quantity" value="<?php echo $cartItem['quantity']; ?>" min="1" class="form-control d-inline" style="width: 80px;">
                                <input type="hidden" name="product_id" value="<?php echo $cartItem['id']; ?>">
                                <button type="submit" name="update" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($cartItem['price'] * $cartItem['quantity'], 2); ?></td>
                        <td>
                            <a href="cart.php?remove=<?php echo $cartItem['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                    <?php $total += $cartItem['price'] * $cartItem['quantity']; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between">
            <h4>Total: $<?php echo number_format($total, 2); ?></h4>
            <a href="index.php" class="btn btn-success">Continue Shopping</a>
        </div>

    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

</body>
</html>
