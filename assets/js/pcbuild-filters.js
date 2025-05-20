//UPDATE PARTS COUNT/TOTAL IN HEADER 
window.addEventListener('DOMContentLoaded', () => {
    const partsCountEl = document.getElementById('parts_count');
    const partsTotalEl = document.getElementById('parts_total_price');

    const parts = localStorage.getItem('cartPartsCount') || 0;
    const total = localStorage.getItem('cartTotal') || 0;

    if (partsCountEl) partsCountEl.textContent = parts;
    if (partsTotalEl) partsTotalEl.textContent = `$${parseFloat(total).toFixed(2)}`;
});
  
// SEARCH FILTER
document.addEventListener("DOMContentLoaded", function () {
const searchInput = document.getElementById("pcbuild-search");
const table = document.getElementById("pcbuild-table");

if (!searchInput || !table) return;

searchInput.addEventListener("input", function () {
    const searchTerm = this.value.toLowerCase();
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach(row => {
        const nameCell = row.querySelector("td:first-child");
        const nameText = nameCell?.textContent.toLowerCase() || "";
        row.style.display = nameText.includes(searchTerm) ? "" : "none";
    });
});


// SEARCH FILTER WITH ZEBRA STRIPE
/* document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("pcbuild-search");
    const table = document.getElementById("pcbuild-table");

    if (!searchInput || !table) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));

    // Function to apply zebra striping
    function applyZebraStriping() {
        const visibleRows = rows.filter(row => row.style.display !== "none");  // Only visible rows
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? '#d4d4d4' : '#ebebeb';  // Apply alternating colors
        });
    }

    // Event listener for the search input field
    searchInput.addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase();  // Get the search term in lowercase

        rows.forEach(row => {
            const nameCell = row.querySelector("td:first-child");  // Assuming product name is in the first column
            const nameText = nameCell ? nameCell.textContent.toLowerCase() : "";  // Get the product name and make it lowercase

            // Display or hide rows based on the search term match
            row.style.display = nameText.includes(searchTerm) ? "" : "none";  // Show or hide row
        });

        // Reapply zebra striping after filtering
        applyZebraStriping();
    });

    // Initial zebra striping on page load
    applyZebraStriping();
}); */

  
// ADD TO BUILDER FUNCTIONALITY
document.querySelectorAll(".add-to-builder").forEach(button => {
    button.addEventListener("click", () => {
        const category = button.dataset.category?.toLowerCase() || 'other';

        const productData = {
            title: button.dataset.title,
            image: button.dataset.image,
            base: button.dataset.base,
            promo: button.dataset.promo,
            shipping: button.dataset.shipping,
            tax: button.dataset.tax,
            availability: button.dataset.availability,
            price: button.dataset.price,
            affiliateUrl: button.dataset.affiliateUrl,
            asin: button.dataset.asin,
            features: button.dataset.features,
            rating: button.dataset.rating,
            socket: button.dataset.socket,
            chipset: button.dataset.chipset,
            category: button.dataset.category
        };

        // Save product to localStorage
        localStorage.setItem(`pcbuild_${category}`, JSON.stringify(productData));

        // Category-specific logic
        switch (category) {
            case 'cpu':
                localStorage.setItem('selected_cpu_socket', productData.socket);
                localStorage.setItem('pcbuild_cpu', JSON.stringify(productData));
                break;
            case 'cpu cooler':
                localStorage.setItem('selected_cpu_cooler_socket', JSON.stringify(productData.socket));
                break;
            case 'motherboard':
                localStorage.setItem('selected_motherboard_socket', productData.socket);
                localStorage.setItem('selected_motherboard_chipset', productData.chipset);
                localStorage.setItem('pcbuild_motherboard', JSON.stringify(productData));
                break;
            case 'memory':
            case 'ram': // just in case you use either
                localStorage.setItem('selected_ram_type', productData.ram_type);
                localStorage.setItem('selected_ram_speed', productData.ram_speed);
                localStorage.setItem('pcbuild_ram', JSON.stringify(productData));
                break;
            default:
                break;
        }

        // UI update or redirect
        if (window.location.pathname.includes("/home")) {
            if (typeof updateRow === "function") {
                updateRow(category, productData);
            }
        } else {
            window.location.href = "/home";
        }
    });
});

// SCROLL TO TABLE ON PAGINATION
const params = new URLSearchParams(window.location.search);
if (params.has('pcbuild_page')) {
    const tableElement = document.getElementById("pcbuild-table");
    if (tableElement) {
        tableElement.scrollIntoView({ behavior: "smooth" });
    }
}
});

//RATING FILTER CHECKBOXES
const ratingFilter = document.getElementById("rating-filter");
if (ratingFilter) {
    ratingFilter.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            // Ensure only one rating is selected at a time
            ratingFilter.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            this.checked = true;

            const selectedValue = this.value;
            const rows = document.querySelectorAll("#pcbuild-table tbody tr");

            rows.forEach(row => {
                const ratingText = row.querySelector("td:nth-child(6)")?.innerText || '';
                const match = ratingText.match(/^(\d(\.\d)?)/);
                const rating = match ? parseFloat(match[1]) : 0;

                let show = false;
                if (selectedValue === 'all') {
                    show = true;
                } else if (selectedValue === 'unrated') {
                    show = rating === 0;
                } else {
                    show = Math.floor(rating) === parseInt(selectedValue);
                }

                row.style.display = show ? '' : 'none';
            });
        });
    });
}

//MANUFATURER FILTER CHECKBOX
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.filter-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const filterGroup = this.closest('.filter-group');
            const options = filterGroup.querySelector('.filter-options');

            if (options.style.display === 'none') {
                options.style.display = 'block';
                this.textContent = '−'; // minus sign
            } else {
                options.style.display = 'none';
                this.textContent = '+'; // plus sign
            }
        });
    });
});