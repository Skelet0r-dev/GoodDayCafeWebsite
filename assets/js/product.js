document.addEventListener("DOMContentLoaded", function () {
  // Containers for each category
  const categoryContainers = {
    "Iced Drink": document.querySelector(".iced-drinks-container"),
    "Hot Drink": document.querySelector(".hot-drinks-container"),
    Frappe: document.querySelector(".frappe-drinks-container"),
    Refresher: document.querySelector(".refresher-drinks-container"),
    Pizza: document.querySelector(".pizza-container"),
    Pasta: document.querySelector(".pasta-container"),
    Pastry: document.querySelector(".pastries-container"),
  };

  const modalsContainer = document.createElement("div"); // Container for modals
  document.body.appendChild(modalsContainer);

  const cartItemsElement = document.getElementById("cartItems"); // Cart view container
  const cartTotalElement = document.getElementById("cartTotal"); // Cart total price
  const cartOffcanvas = new bootstrap.Offcanvas(document.getElementById("cart")); // Offcanvas instance
  let cartItems = [];
  let total = 0;

  function applyDiscount(amount) {
    const el = document.getElementById('userStatus');
    const status = el ? (el.value || '').trim().toUpperCase() : '';
    return (status === 'PWD' || status === 'SENIOR') ? amount * 0.9 : amount;
  }

  function computeBaseTotal() {
    return cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
  }

  function updateCartUI() {
    total = 0;
    const rowsHTML = [];

    for (let i = 0; i < cartItems.length; i += 2) {
      const rowItems = cartItems.slice(i, i + 2).map((item, rowIndex) => {
        total += item.price * item.quantity;
        return `
          <div>
            <p class="list-group-item d-flex justify-content-between align-items-center">
              ${item.name} (₱${item.price} x ${item.quantity})
              <span class="remove-item-btn text-danger" style="cursor: pointer;" data-index="${rowIndex + i}">&times;</span>
            </p>
          </div>`;
      }).join("");
      rowsHTML.push(rowItems);
    }

    cartItemsElement.innerHTML = rowsHTML.join("");

    const discountedTotal = applyDiscount(total);

    cartTotalElement.textContent = `₱${discountedTotal.toFixed(2)}`;
    if ('value' in cartTotalElement) cartTotalElement.value = discountedTotal.toFixed(2);

    // Keep hidden inputs in sync
    const cartInput = document.getElementById("cartItemsInput");
    const totalInput = document.getElementById("totalPriceInput");
    if (cartInput) cartInput.value = JSON.stringify(cartItems);
    if (totalInput) totalInput.value = discountedTotal.toFixed(2);
  }

  (function () {
    const form = document.getElementById("checkoutForm");
    const paymentInput = document.getElementById("paymentAmountInput");
    const totalPriceInput = document.getElementById("totalPriceInput");

    function cartIsEmpty() {
      // Prefer the authoritative hidden input if present
      const cartInput = document.getElementById("cartItemsInput");
      if (cartInput && cartInput.value) {
        try {
          const arr = JSON.parse(cartInput.value);
          return !Array.isArray(arr) || arr.length === 0;
        } catch (e) {
          return !Array.isArray(cartItems) || cartItems.length === 0;
        }
      }
      return !Array.isArray(cartItems) || cartItems.length === 0;
    }

    function paymentIsEnough() {
      const discounted = applyDiscount(computeBaseTotal());
      totalPriceInput.value = discounted.toFixed(2);
      const pay = parseFloat(paymentInput.value) || 0;
      const totalDue = parseFloat(totalPriceInput.value) || 0;
      return pay >= totalDue;
    }

    form.addEventListener("submit", function (e) {
      // recompute discounted total right before submit
      const discounted = applyDiscount(computeBaseTotal());
      totalPriceInput.value = discounted.toFixed(2);

      if (cartIsEmpty()) {
        e.preventDefault();
        alert("Your cart is empty. Please add items before checking out.");
        return;
      }
      if (!paymentIsEnough()) {
        e.preventDefault();
        alert("Payment amount is less than the total price. Please enter enough to cover the total.");
        paymentInput.focus();
      }
    });
  })();

  // Function to add an item to the cart
  function addToCart(product) {
    const existingItemIndex = cartItems.findIndex((item) => item.name === product.name);

    if (existingItemIndex !== -1) {
      cartItems[existingItemIndex].quantity += product.quantity;
    } else {
      cartItems.push(product);
    }

    updateCartUI();

    const cartElement = document.querySelector('#cart');
    if (cartElement) {
      const cartInstance = bootstrap.Offcanvas.getOrCreateInstance(cartElement);
      cartInstance.show();
      console.log("Cart offcanvas opened programmatically after adding a product.");
    } else {
      console.error("Cart offcanvas element not found.");
    }
  }

  // Remove item handler (delegated)
  document.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("remove-item-btn")) {
      const idx = parseInt(e.target.getAttribute("data-index"), 10);
      if (!Number.isNaN(idx)) {
        cartItems.splice(idx, 1);
        updateCartUI();
        cartOffcanvas.show();
      }
    }
  });

  // Function to create a product card
  function createProductCard(product) {
    const cardContainer = document.createElement("div");
    cardContainer.className = "col-12 col-sm-6 col-md-4 col-lg-3 mb-4";

    cardContainer.innerHTML = `
      <div class="card text-center" style="background-color: rgb(92, 78, 59);">
        <div class="card-img-container beige-custom" style="height: 200px; overflow: hidden; background-color: rgb(237, 223, 203);">
          <img src="${product.FILEPATH}" alt="${product.PRODUCT_NAME}" class="card-img-top" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <div class="card-body">
          <h5 class="card-title beige-custom">${product.PRODUCT_NAME}</h5>
          <p class="card-text beige-custom">${product.DESCRIPTION}</p>
          <button type="button" class="btn btn-cart" data-bs-toggle="modal" data-bs-target="#modal-${product.PRODUCT_ID}">
            Add to Cart <i class="bi bi-cart-fill"></i>
          </button>
        </div>
      </div>
    `;

    createProductModal(product, cardContainer);
    return cardContainer;
  }

  // Function to create a modal for a product
  function createProductModal(product) {
    const modal = document.createElement("div");
    modal.className = "modal fade";
    modal.id = `modal-${product.PRODUCT_ID}`;
    modal.setAttribute("tabindex", "-1");
    modal.setAttribute("aria-hidden", "true");

    modal.innerHTML = `
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${product.PRODUCT_NAME}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-center">
              <img src="${product.FILEPATH}" alt="${product.PRODUCT_NAME}" class="w-100 mb-3" style="height: 300px; object-fit: contain; border-radius: 8px;">
            </div>
            <p><strong>Description:</strong> ${product.DESCRIPTION}</p>
            <p><strong>Price:</strong> ₱${product.PRICE}</p>

            <div class="quantity-selector d-flex align-items-center gap-3 justify-content-center mt-3">
              <button type="button" class="quantity-btn decrement-btn btn btn-outline-secondary">-</button>
              <span class="quantity-display">1</span>
              <button type="button" class="quantity-btn increment-btn btn btn-outline-secondary">+</button>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary add-to-cart-modal-btn" 
              data-product-id="${product.PRODUCT_ID}"
              data-product-name="${product.PRODUCT_NAME}" 
              data-product-price="${product.PRICE}">
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    `;

    let quantity = 1;
    const decrementBtn = modal.querySelector(".decrement-btn");
    const incrementBtn = modal.querySelector(".increment-btn");
    const quantityDisplay = modal.querySelector(".quantity-display");

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

    const addToCartBtn = modal.querySelector(".add-to-cart-modal-btn");
    addToCartBtn.addEventListener("click", () => {
      addToCart({ name: product.PRODUCT_NAME, price: product.PRICE, quantity, id: product.PRODUCT_ID });
      const bootstrapModal = bootstrap.Modal.getInstance(modal);
      bootstrapModal.hide();
    });

    modalsContainer.appendChild(modal);
  }

  // Process products and categorize cards
  products.forEach((product) => {
    if (categoryContainers[product.PRODUCT_CATEGORY]) {
      const card = createProductCard(product);
      categoryContainers[product.PRODUCT_CATEGORY].appendChild(card);
    } else {
      console.warn(`Unknown category: ${product.PRODUCT_CATEGORY}`);
    }
  });
});

// Nav glow for desktop (ignore non-toggle buttons)
document.addEventListener("DOMContentLoaded", function () {
  const navButtons = document.querySelectorAll(".toggle-nav");

  navButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const isActive = this.classList.contains("active-glow");
      navButtons.forEach((btn) => btn.classList.remove("active-glow"));
      if (!isActive) {
        this.classList.add("active-glow");
      }
    });
  });

  window.addEventListener("scroll", function () {
    navButtons.forEach((btn) => btn.classList.remove("active-glow"));
  });
});

// Cart glow toggle
document.addEventListener("DOMContentLoaded", function () {
  const navButtons = document.querySelectorAll(".toggle-nav-cart");

  navButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const offcanvasTarget = this.getAttribute("data-bs-target");
      const offcanvasElement = document.querySelector(offcanvasTarget);

      if (offcanvasElement) {
        offcanvasElement.addEventListener("shown.bs.offcanvas", () => {
          navButtons.forEach((btn) => btn.classList.remove("active-glow"));
          this.classList.add("active-glow");
        });

        offcanvasElement.addEventListener("hidden.bs.offcanvas", () => {
          this.classList.remove("active-glow");
        });
      }
    });
  });
});

// Offcanvas scroll behavior (disable auto-hide on small screens)
document.addEventListener("DOMContentLoaded", function () {
  const offcanvasElement = document.getElementById("cart");
  if (!offcanvasElement) return;
  const offcanvasInstance = new bootstrap.Offcanvas(offcanvasElement);
  const navbarElement = document.querySelector("#navbar");

  function handleScrollHideOffcanvas() {
    if (window.innerWidth <= 768) return; // disable auto-hide on small screens
    const scrollPosition = window.scrollY;
    const navbarHeight = navbarElement ? navbarElement.offsetHeight : 0;
    if (scrollPosition < navbarHeight + 400) {
      offcanvasInstance.hide();
    }
  }

  window.addEventListener("scroll", handleScrollHideOffcanvas);
});