<script>
        // SORTING LOGIC
        document.addEventListener('DOMContentLoaded', () => {
            const table = document.getElementById("pcbuild-table");
            if (!table) return;

            const headers = table.querySelectorAll(".sortable-header");

            let currentSort = { key: null, direction: 'asc' };

            headers.forEach(header => {
                header.addEventListener('click', function () {
                    const key = this.dataset.key;
                    currentSort.direction = (currentSort.key === key && currentSort.direction === 'asc') ? 'desc' : 'asc';
                    currentSort.key = key;

                    // Reset all header icons
                    headers.forEach(h => {
                        h.innerHTML = `&#9654; ${h.textContent.trim().replace(/^▲|▼|▶/, '')}`;
                    });

                    // Show active arrow direction
                    this.innerHTML = `${currentSort.direction === 'asc' ? '▲' : '▼'} ${this.textContent.trim().replace(/^▲|▼|▶/, '')}`;

                    // Sort rows by selected column
                    sortTableByKey(key, currentSort.direction);
                });
            });

            function sortTableByKey(key, direction) {
                const tbody = table.querySelector("tbody");
                const rows = Array.from(tbody.querySelectorAll("tr"));

                rows.sort((a, b) => {
                    const getText = row => row.querySelector(`td:nth-child(${getColumnIndex(key)})`)?.innerText.trim().toLowerCase() || '';
                    const valA = getText(a);
                    const valB = getText(b);

                    // Try parsing numbers for numeric sort
                    const numA = parseFloat(valA.replace(/[^\d.]/g, ''));
                    const numB = parseFloat(valB.replace(/[^\d.]/g, ''));

                    if (!isNaN(numA) && !isNaN(numB)) {
                        return direction === 'asc' ? numA - numB : numB - numA;
                    }

                    return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
                });

                // Apply row backgrounds again after sort
                rows.forEach((row, i) => {
                    row.style.backgroundColor = (i % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                    tbody.appendChild(row);
                });
            }

            // Column index mapping
            function getColumnIndex(key) {
                const mapping = {
                    name: 1,
                    fan_rpm: 2,
                    noise: 3,
                    radiator: 4,
                    rating: 5,
                    price: 6
                };
                return mapping[key];
            }
        });
    </script>

    <!-- PRICE RANGE SLIDER FILTER -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const table = document.getElementById("pcbuild-table");
            const sliderContainer = document.getElementById("price-slider");
            const minLabel = document.getElementById("price-min-label");
            const maxLabel = document.getElementById("price-max-label");

            if (!table || !sliderContainer) return;

            const rows = Array.from(table.querySelectorAll("tbody tr"));
            const prices = rows.map(row => {
                const priceText = row.querySelector("td:nth-child(6)")?.textContent.replace(/[^0-9.]/g, '') || "0";
                return parseFloat(priceText) || 0;
            });

            const minPrice = Math.floor(Math.min(...prices));
            const maxPrice = Math.ceil(Math.max(...prices));
            let currentMin = minPrice;
            let currentMax = maxPrice;

            minLabel.textContent = `$${minPrice}`;
            maxLabel.textContent = `$${maxPrice}`;

            sliderContainer.innerHTML = `
                <input type="range" id="min-price" min="${minPrice}" max="${maxPrice}" value="${minPrice}" step="1" style="width: 100%;">
                <input type="range" id="max-price" min="${minPrice}" max="${maxPrice}" value="${maxPrice}" step="1" style="width: 100%; margin-top: 10px;">
            `;

            const minSlider = document.getElementById("min-price");
            const maxSlider = document.getElementById("max-price");

            function filterByPrice() {
                const minVal = parseFloat(minSlider.value);
                const maxVal = parseFloat(maxSlider.value);

                minLabel.textContent = `$${minVal}`;
                maxLabel.textContent = `$${maxVal}`;

                rows.forEach(row => {
                    const priceText = row.querySelector("td:nth-child(6)")?.textContent.replace(/[^0-9.]/g, '') || "0";
                    const price = parseFloat(priceText) || 0;
                    row.style.display = (price >= minVal && price <= maxVal) ? "" : "none";
                });
            }

            minSlider.addEventListener("input", () => {
                if (parseFloat(minSlider.value) > parseFloat(maxSlider.value)) {
                    minSlider.value = maxSlider.value;
                }
                filterByPrice();
            });

            maxSlider.addEventListener("input", () => {
                if (parseFloat(maxSlider.value) < parseFloat(minSlider.value)) {
                    maxSlider.value = minSlider.value;
                }
                filterByPrice();
            });

            filterByPrice();
        });
    </script>