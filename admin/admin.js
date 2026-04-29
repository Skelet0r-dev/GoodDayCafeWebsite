document.addEventListener("DOMContentLoaded", function () {
    // Containers for each category
    const icedDrinksContainer = document.querySelector(".iced-drinks-container");
    const hotDrinksContainer = document.querySelector(".hot-drinks-container");
    const frappeDrinksContainer = document.querySelector(".frappe-drinks-container");
    const refresherDrinksContainer = document.querySelector(".refresher-drinks-container");
    const pizzaContainer = document.querySelector(".pizza-container");
    const pastaContainer = document.querySelector(".pasta-container");
    const pastriesContainer = document.querySelector(".pastries-container");

    const modalsContainer = document.createElement("div"); // Container for modals
    document.body.appendChild(modalsContainer);

    function getImageUrl(filepath) {
        if (!filepath) {
            return "";
        }

        const normalizedPath = filepath.replace(/\\/g, "/");
        const uploadsIndex = normalizedPath.indexOf("/uploads/");

        return uploadsIndex === -1 ? normalizedPath : normalizedPath.slice(uploadsIndex);
    }

    // Function to create a product card
    function createProductCard(product) {
    const cardContainer = document.createElement("div");
    cardContainer.className = "col-12 col-sm-6 col-md-4 col-lg-3 d-flex flex-column align-items-stretch";

    cardContainer.innerHTML = `
        <!-- Main Card -->
        <div class="card w-100 h-100 text-center position-relative" style="background-color: rgb(92, 78, 59);">
            <div class="card-img-container beige-custom" style="
                overflow: hidden;
                height: 200px;
                box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.5);
                background-color: rgb(237, 223, 203);
            ">
                <img src="${getImageUrl(product.filepath)}" alt="${product.product_name}" class="card-img-top" style="
                    padding-top: 10px;
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                    transition: all 0.3s ease-in-out;
                ">
            </div>
            <div class="card-body d-flex flex-column py-4">
                <h5 class="card-title beige-custom">${product.product_name}</h5>
                <p class="card-text beige-custom">${product.description}</p>
                <div class="mt-auto d-flex gap-2 justify-content-center">
                    <!-- Add to Cart Button -->
                    <button type="button" class="btn btn-cart" data-bs-toggle="modal" data-bs-target="#modal-${product.product_id}">
                        Add to Cart <i class="bi bi-cart-fill"></i>
                    </button>
                </div>
            </div>
        </div>


        <!-- Remove Button -->
        <button type="button" class="btn btn-danger btn-remove" style="
            width: 100%;
            font-size: 16px;
            font-weight: bold;
        ">

            Remove ${product.product_name}
        </button>
    `;

    // Add event listener to Remove Button
    const removeBtn = cardContainer.querySelector(".btn-remove");
    removeBtn.addEventListener("click", () => {
        if (confirm(`Are you sure you want to delete ${product.product_name}?`)) {
            deleteProduct(product.product_id, cardContainer);
        }
    });

    // Create modal for the product
    createProductModal(product);

    return cardContainer;
}



    // Function to create the modal for a product
    function createProductModal(product) {
        const modalId = `modal-${product.product_id}`;

        const modal = document.createElement("div");
        modal.className = "modal fade";
        modal.id = modalId;
        modal.setAttribute("tabindex", "-1");
        modal.setAttribute("aria-hidden", "true");

        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${product.product_name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <img src="${getImageUrl(product.filepath)}" alt="${product.product_name}" style="width: 100%; height: 300px; object-fit: contain; border-radius: 8px; margin-bottom: 20px;">
                        </div>

                    <div class="container justify-content-center d-flex flex-column align-items-center">
                        <p><strong>Description:</strong> ${product.description}</p>
                        <p><strong>Price:</strong> ₱${product.price}</p>
                        
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn decrement-btn">-</button>
                            <span class="quantity-display">1</span>
                            <button type="button" class="quantity-btn increment-btn">+</button>
                        </div>
                    </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Add to Cart</button>
                    </div>
                </div>
            </div>
        `;

        // Add quantity selection functionality
        const decrementBtn = modal.querySelector(".decrement-btn");
        const incrementBtn = modal.querySelector(".increment-btn");
        const quantityDisplay = modal.querySelector(".quantity-display");
        let quantity = 1;

        decrementBtn.addEventListener("click", () => {
            if (quantity > 1) {
                quantity--;
                quantityDisplay.textContent = quantity;
            }
        });

        incrementBtn.addEventListener("click", () => {
            quantity++;
            quantityDisplay.textContent = quantity;
        });

        modalsContainer.appendChild(modal);
    }

    // Function to delete a product via an HTTP POST request (PHP backend)
    function deleteProduct(productId, containerElement) {
        fetch("delete_product.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `product_id=${encodeURIComponent(productId)}`,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    containerElement.remove(); // Remove the container
                    alert("Product deleted successfully!");
                } else {
                    alert("Failed to delete product.");
                }
            })
            .catch((error) => console.error("Error:", error));
    }

    // Loop through products and categorize them
    products.forEach((product) => {
        const card = createProductCard(product);

        if (product.product_category === "Iced Drink") {
            icedDrinksContainer.appendChild(card); // Add to Iced Drinks Row
        } else if (product.product_category === "Hot Drink") {
            hotDrinksContainer.appendChild(card); // Add to Hot Drinks Row
        } else if (product.product_category === "Frappe") {
            frappeDrinksContainer.appendChild(card); // Add to Frappe's Row
        } else if (product.product_category === "Pastry") {
            pastriesContainer.appendChild(card); // Add to Pastry's Row
        } else if (product.product_category === "Pasta") {
            pastaContainer .appendChild(card); // Add to Pasta's Row
        } else if (product.product_category === "Pizza") {
            pizzaContainer.appendChild(card); // Add to Pizza's Row
        } else if (product.product_category === "Refresher") {
            refresherDrinksContainer.appendChild(card); // Add to Refresher's Row
        } else {
            console.warn(`Unknown category: ${product.product_category}`);
        }
    });
});





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



document.addEventListener("DOMContentLoaded", function () {
    // Get all nav buttons with the class 'toggle-nav'
    const navButtons = document.querySelectorAll(".toggle-nav");

    navButtons.forEach((button) => {
        // Add click event listener to each button
        button.addEventListener("click", function () {
            // Remove the 'active-glow' class from all buttons
            navButtons.forEach((btn) => btn.classList.remove("active-glow"));

            // Add 'active-glow' to the clicked button
            this.classList.add("active-glow");
        });
    });
});
