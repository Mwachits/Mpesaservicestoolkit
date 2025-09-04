// js/script.js

let selectedService = null;

document.addEventListener("DOMContentLoaded", function () {
  loadServices();

  document.getElementById("modalClose").addEventListener("click", closeModal);
  window.addEventListener("click", function (event) {
    if (event.target === document.getElementById("customModal")) {
      closeModal();
    }
  });

  document
    .getElementById("mpesaForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();

      if (!selectedService) {
        openModal(
          "<h2>❌ Error</h2><p>Please select a service first.</p>"
        );
        return;
      }

      const phone = document.getElementById("phoneNumber").value;
      const name = document.getElementById("customerName").value;

      if (!phone || !name) {
        openModal(
          "<h2>❌ Error</h2><p>Please fill in all required fields.</p>"
        );
        return;
      }

      const s = services[selectedService];
      document.getElementById("loading").style.display = "block";
      document.getElementById("payButton").disabled = true;

      const formData = new FormData();
      formData.append("service_type", selectedService);
      formData.append("phone_number", phone);
      formData.append("customer_name", name);
      formData.append("amount", s.amount);

      fetch("process_payment.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          document.getElementById("loading").style.display = "none";
          document.getElementById("payButton").disabled = false;

          if (data.success) {
            openModal(`
              <h2>✅ Payment Request Sent</h2>
              <p>Please check your phone and enter your M-Pesa PIN to complete the payment.</p>
              <p><strong>Amount:</strong> KES ${s.amount.toLocaleString()}</p>
              <p><strong>Service:</strong> ${s.name}</p>
            `);
          } else {
            openModal(`<h2>❌ Payment Failed</h2><p>${data.message || "Please try again."}</p>`);
          }
        })
        .catch(() => {
          document.getElementById("loading").style.display = "none";
          document.getElementById("payButton").disabled = false;
          openModal(
            "<h2>❌ Network Error</h2><p>Check your connection and try again.</p>"
          );
        });
    });
});

function loadServices() {
  const serviceGrid = document.getElementById("serviceGrid");
  Object.keys(services).forEach((key) => {
    const s = services[key];
    const div = document.createElement("div");
    div.className = "service-card";
    div.innerHTML = `
      <div class="service-name">${s.name}</div>
      <div class="service-amount">KES ${s.amount.toLocaleString()}</div>
      <div class="service-description">${s.description}</div>
      <div class="service-time">📅 ${s.processing_time}</div>
    `;
    div.onclick = () => selectService(key, div);
    serviceGrid.appendChild(div);
  });
}

function selectService(serviceKey, cardElement) {
  document
    .querySelectorAll(".service-card")
    .forEach((card) => card.classList.remove("selected"));
  cardElement.classList.add("selected");

  selectedService = serviceKey;
  const s = services[serviceKey];

  document.getElementById("selectedServiceName").textContent = s.name;
  document.getElementById("selectedServiceDescription").textContent =
    s.description;
  document.getElementById("selectedServiceTime").textContent = `Processing: ${s.processing_time}`;
  document.getElementById("amountDisplay").textContent = s.amount.toLocaleString();
  document.getElementById("paymentForm").style.display = "block";
  document.getElementById("paymentForm").scrollIntoView({ behavior: "smooth" });
}

function scrollToServices() {
  document.getElementById("serviceGrid").scrollIntoView({ behavior: "smooth" });
}

function openModal(contentHTML) {
  document.getElementById("modalBody").innerHTML = contentHTML;
  document.getElementById("customModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("customModal").style.display = "none";
}
