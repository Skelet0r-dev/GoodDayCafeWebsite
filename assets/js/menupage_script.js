

function updateDisplay() {
    document.getElementById('quantityDisplay').textContent = quantity;
    document.getElementById('decreaseBtn').disabled = quantity <= minQuantity;
    document.getElementById('increaseBtn').disabled = quantity >= maxQuantity;
}

function decreaseQuantity() {
    if (quantity > minQuantity) {
        quantity--;
        updateDisplay();
    }
}

function increaseQuantity() {
    if (quantity < maxQuantity) {
        quantity++;
        updateDisplay();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateDisplay();
});




// ---------------- Cart System ---------------- //
let cart = [];

function updateCartUI() {
    const cartBody = document.querySelector("#cart .offcanvas-body");

    if (cart.length === 0) {
        cartBody.innerHTML = `<p>Your cart is empty. Add items to see them here.</p>`;
        return;
    }

    let html = `<ul class="list-group mb-3">`;

    cart.forEach((item) => {
        html += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${item.name}</strong><br>
                    Qty: ${item.quantity} × $${item.price}
                </div>
                <span class="badge bg-dark rounded-pill">$${(item.price * item.quantity).toFixed(2)}</span>
            </li>
        `;
    });

    html += `</ul>`;

    let total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    html += `<h5 class="text-end">Total: $${total.toFixed(2)}</h5>`;

    cartBody.innerHTML = html;
}



// ---------------- Add to Cart Handler ---------------- //
document.addEventListener("click", function (e) {

    // If button has .add-to-cart class
    if (e.target.classList.contains("add-to-cart")) {

        let name = e.target.dataset.name;
        let price = parseFloat(e.target.dataset.price);

        // If button came from modal → use modal quantity
        let selectedQuantity = quantity;

        // If the button has data-qty, override (ex: cards that add 1)
        if (e.target.dataset.qty) {
            selectedQuantity = parseInt(e.target.dataset.qty);
        }

        // Add / update item in cart
        let existing = cart.find(item => item.name === name);

        if (existing) {
            existing.quantity += selectedQuantity;
        } else {
            cart.push({
                name: name,
                price: price,
                quantity: selectedQuantity
            });
        }

        updateCartUI();

        // Reset modal quantity after adding
        quantity = 1;
        updateDisplay();
    }
});



