<?php
function aawp_pcbuild_display_parts_gpu($atts) {
    $atts = shortcode_atts(array('category' => 'video-card'), $atts);
    $input_category = sanitize_title($atts['category']);
    
    $category_map = [
        'gpu' => 'Video Card',
        'video-card' => 'Video Card',
    ];
    
    $category = $category_map[$input_category] ?? 'Video Card';
    
    // Create transient key
    $transient_key = 'aawp_pcbuild_cache_' . md5($category);
    
    // Clear cache if admin and ?clear_cache=1 in URL
    if (is_user_logged_in() && current_user_can('manage_options') && isset($_GET['clear_cache'])) {
        delete_transient($transient_key);
    }
    
    // Try to get products from cache
    $products = get_transient($transient_key);
    
    // If no cached products, fetch and cache them
    if ($products === false) {
        $products = aawp_pcbuild_get_products($category);
        set_transient($transient_key, $products, 12 * HOUR_IN_SECONDS);
    }
    
    // If still no products, show error
    if (!is_array($products) || empty($products['SearchResult']['Items'])) {
        return '<p class="aawp-error">No products found or error fetching data. Please try again later.</p>';
    }    

    $all_items = $products['SearchResult']['Items'];
    $total_items = count($all_items);
    $items_per_page = 100;
    $current_page = isset($_GET['pcbuild_page']) ? max(1, intval($_GET['pcbuild_page'])) : 1;
    $total_pages = ceil($total_items / $items_per_page);
    $start = ($current_page - 1) * $items_per_page;
    $display_items = array_slice($all_items, $start, $items_per_page);

    ob_start();
	include('parts-header.php');
    ?>
    <div style="background-color:#41466c; padding:40px; color:#fff; font-size:24px; font-weight:bold; text-align:center; margin-bottom:40px">
        Choose A <?php echo esc_html($category); ?>
    </div>
    <div style="width:90%; margin:0 auto; font-family:sans-serif;">
    <div class="pcbuilder-container" style="display:flex; gap:20px; margin-top:20px;">
            <!-- Sidebar -->
            <div class="pcbuild-sidebar" style="width:250px; background:#f9f9f9; padding:20px; border-radius:8px;">
                <div style="margin-bottom:20px;"><strong>Part</strong> | <strong>List</strong></div>
                <div style="margin-bottom:20px;"><label><input type="checkbox" checked disabled /> Compatibility Filter</label></div>
                <div style="margin-bottom:20px;">
                    <div>PARTS: <strong id="parts_count"></strong></div>
                    <div>TOTAL: <strong id="parts_total_price"></strong></div>
                </div>
                <div class="filter-group">
                    <div class="filter-header">
                        <strong>PRICE</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="price-filter" style="display: block;">
                        <div id="price-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="price-min-label">$0</span>
                            <span id="price-max-label">$0</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>MANUFACTURER</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="manufacturer-filter">
                        <label><input type="checkbox" id="manufacturer-all" checked> All</label><br/>
                        <!-- Checkboxes will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>SELLER RATING</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="rating-filter">
                        <!-- Filters will be injected here -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>CHIPSET</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="chipset-filter">
                        <label><input type="checkbox" id="chipset-all" checked> All</label><br/>
                        <!-- Checkboxes for different chipsets will be inserted here by JS -->
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Memory</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="memory-filter" style="display: block;">
                        <div id="memory-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="memory-min-label">0 GB</span>
                            <span id="memory-max-label">0 GB</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Core Clock</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="coreclock-filter" style="display: block;">
                        <div id="coreclock-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="coreclock-min-label">0 MHz</span>
                            <span id="coreclock-max-label">0 MHz</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Boost Clock</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="boostclock-filter" style="display: block;">
                        <div id="boostclock-slider" style="margin-top: 15px;"></div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-top: 6px;">
                            <span id="boostclock-min-label">0 MHz</span>
                            <span id="boostclock-max-label">0 MHz</span>
                        </div>
                    </div>
                </div>
                <div class="filter-group" style="margin-bottom: 20px; margin-top:20px;">
                    <div class="filter-header">
                        <strong>Color</strong>
                        <button class="filter-toggle">−</button>
                    </div>
                    <div class="filter-options" id="color-filter">
                        <label><input type="checkbox" id="color-all" checked> All</label><br/>
                        <!-- Checkboxes for different colors will be inserted here by JS -->
                    </div>
                </div>

            </div>

            <!-- Main Table Section -->
            <div class="pcbuilder-main" style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="font-weight:bold;"><?php echo $total_items; ?> Products</div>
                    <div>
                        <input type="text" id="pcbuild-search" placeholder="Search..." style="padding:6px 10px; border-radius:6px; border:1px solid #ccc; margin-bottom: 15px" />
                    </div>
                </div>

                <table id="pcbuild-table" style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f0f0f0;">
                        <tr>
                            <th class="sortable-header" data-key="name">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Name
                                </span>
                            </th>                           
                            <th class="sortable-header" data-key="chipset">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Chipset
                                </span>
                            </th>
                            <th class="sortable-header" data-key="memory">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Memory
                                </span>
                            </th>
                            <th class="sortable-header" data-key="core_clock">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Core Clock
                                </span>
                            </th>
                            <th class="sortable-header" data-key="boost_clock">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Boost Clock
                                </span>
                            </th>
                            <th class="sortable-header" data-key="color">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Color
                                </span>
                            </th>
                            <!-- <th class="sortable-header" data-key="length">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Length
                                </span>
                            </th> -->
                            <th class="sortable-header" data-key="rating">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span>Seller Rating
                                </span>
                            </th>
                            <th class="sortable-header" data-key="price">
                                <span class="sort-header-label">
                                    <span class="sort-arrow">&#9654;</span> Price
                                </span>
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <?php include('rating-count.php'); ?>
                    <tbody>
                    <?php foreach ($display_items as $index => $item):
                            $row_bg = ($index % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                            $asin = $item['ASIN'] ?? '';
                            $full_title = $item['ItemInfo']['Title']['DisplayValue'] ?? 'Unknown Product';
                            $title = esc_html(implode(' ', array_slice(explode(' ', $full_title), 0, 4)));
                            $raw_title = esc_attr($full_title);
                            $image = $item['Images']['Primary']['Large']['URL'] ?? '';
                            $price = $item['Offers']['Listings'][0]['Price']['DisplayAmount'] ?? 'N/A';
                            $base_price = $price;
                            $availability = $item['Offers']['Listings'][0]['Availability']['Message'] ?? 'In Stock';
                            $product_url = $item['DetailPageURL'] ?? '#';
                            $features = $item['ItemInfo']['Features']['DisplayValues'] ?? [];
                            $features_string = implode(' ', $features);
                            $manufacturer = $item['ItemInfo']['ByLineInfo']['Manufacturer']['DisplayValue'] ?? 'Unknown';
                            $feedbackCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                            $sellerCount = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackCount'] ?? 'Unknown';
                            $sellerRating = $item['Offers']['Listings'][0]['MerchantInfo']['FeedbackRating'] ?? 'Unknown';

                            // Append title to features string for better matching
                            $combined_string = $features_string . ' ' . $full_title . ' ' . ($item['ItemInfo']['ProductInfo']['Size']['DisplayValue'] ?? '');

                            // Extract GPU attributes
                            preg_match('/(?:NVIDIA\s+)?(GeForce\s+RTX\s?\d{3,4}|GeForce\s+GTX\s?\d{3,4})|(?:AMD\s+)?(Radeon\s+RX\s?\d{3,4}|Radeon\s+HD\s?\d{3,4})/i', $combined_string, $chipset_match);
                            preg_match('/(\d+)\s*GB/i', $features_string . ' ' . $full_title, $memory_match);
                            preg_match('/(?:Core Clock|GPU Clock Speed|Base Clock|Heart stroke|OC Mode|Gaming Mode)[^0-9]{0,10}(\d{3,5})\s*MHz/i', $combined_string, $core_match);
                            $core = isset($core_match[1]) ? $core_match[1] . ' MHz' : '-';
                            preg_match('/Boost Clock[:\s]*([\d,]+)\s*MHz/i', $combined_string, $boost_match);
                            preg_match('/(Black|White|Red|Blue|Silver|Gray|RGB)/i', $combined_string, $color_match);
                            //preg_match('/L\s*=\s*(\d{2,3})\s*mm/i', $combined_string, $length_match);

                            $chipset = $chipset_match[0] ?? '-';
                            $memory = isset($memory_match[1]) ? $memory_match[1] . 'GB' : '-';
                            $core = isset($core_match[1]) ? $core_match[1] . ' MHz' : '-';
                            $boost = isset($boost_match[1]) ? $boost_match[1] . ' MHz' : '-';
                            $color = $color_match[1] ?? '-';
                            //$length = $length_match[1] ?? '-';
                            $rating_count = display_rating_and_count($sellerRating, $sellerCount);
                        ?>

                        <tr style="background-color: <?php echo $row_bg; ?>; border-bottom:1px solid #DDD; font-size: 14px">
                            <td style="font-weight:800; padding:10px; display:flex; align-items:center; gap:10px;" title="<?php echo $raw_title; ?>">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo $title; ?>" style="width:100px; height:100px; object-fit:cover; border-radius:4px;" />
                                <?php echo $title; ?>
                            </td>
                            <td style="padding:10px;"><?php echo esc_html($chipset); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($memory); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($core); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($boost); ?></td>
                            <td style="padding:10px;"><?php echo esc_html($color); ?></td>
                            <!-- <td style="padding:10px;"><?php //echo esc_html($length); ?> mm</td> -->
                            <td style="padding:10px;" data-rating="<?php echo isset($sellerRating) ? esc_attr($sellerRating) : ''; ?>"><?php echo $rating_count; ?></td>
                            <td style="padding:10px;"><?php echo esc_html($price); ?></td>
                            <td style="padding:10px;">
                                <button class="add-to-builder"
                                    data-asin="<?php echo esc_attr($asin); ?>"
                                    data-title="<?php echo esc_attr($full_title); ?>"
                                    data-image="<?php echo esc_url($image); ?>"
                                    data-base="<?php echo esc_attr($base_price); ?>"
                                    data-shipping="FREE"
                                    data-availability="<?php echo esc_attr($availability); ?>"
                                    data-price="<?php echo esc_attr($base_price); ?>"
                                    data-category="<?php echo esc_attr($category); ?>"
                                    data-affiliate-url="<?php echo esc_url($product_url); ?>"
                                    data-features="<?php echo esc_attr(implode(', ', $features)); ?>"
                                    data-manufacturer="<?php echo esc_attr($manufacturer); ?>"
                                    data-chipset="<?php echo esc_attr($chipset); ?>"
                                    data-memory="<?php echo esc_attr($memory); ?>"
                                    data-core-clock="<?php echo esc_attr($core); ?>"
                                    data-boost-clock="<?php echo esc_attr($boost); ?>"
                                    data-color="<?php echo esc_attr($color); ?>"
                                    data-rating="<?php echo isset($sellerRating) ? esc_attr($ratsellerRatinging) : ''; ?>"
                                    style="padding:10px 18px; background-color:#28a745; color:#fff; border:none; border-radius:5px; cursor:pointer;">
                                    <?php _e('Add', 'aawp-pcbuild'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="margin-top: 20px; text-align: center;">
                        <?php for ($i = 1; $i <= $total_pages; $i++):
                            $url = add_query_arg('pcbuild_page', $i);
                            $is_active = ($i === $current_page);
                        ?>
                            <a href="<?php echo esc_url($url); ?>"
                                style="margin: 0 5px; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none;
                                <?php echo $is_active ? 'background-color: #007bff; color: white;' : 'color: #007bff;'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 768px) {
            .pcbuilder-container {
                flex-direction: column;
            }
            .pcbuild-sidebar,
            .pcbuilder-main {
                width: 100% !important;
            }
            .pcbuilder-main {
                max-height: 80vh; /* Adjust based on your layout */
                overflow-y: auto;
            }
        }
    </style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const ratingRanges = {
        "5": { min: 4.5, max: 5.0 },
        "4": { min: 3.5, max: 4.4 },
        "3": { min: 2.5, max: 3.4 },
        "unrated": "unrated"
    };

    const ratingFilterContainer = document.getElementById("rating-filter");
    const productRows = document.querySelectorAll("#pcbuild-table tbody tr");

    const ratingOptions = [
        { value: "all", label: "All" },
        { value: "5", label: "★★★★★" },
        { value: "4", label: "★★★★☆" },
        { value: "3", label: "★★★☆☆" },
        { value: "unrated", label: "Unrated" }
    ];

    // Inject rating checkboxes
    ratingFilterContainer.innerHTML = "";
    ratingOptions.forEach(opt => {
        const label = document.createElement("label");
        const input = document.createElement("input");
        input.type = "checkbox";
        input.name = "rating";
        input.value = opt.value;
        if (opt.value === "all") input.checked = true;
        label.style.display = "block";
        label.style.margin = "4px 0";
        label.appendChild(input);
        label.insertAdjacentHTML("beforeend", ` ${opt.label}`);
        ratingFilterContainer.appendChild(label);
    });

    const ratingFilterInputs = document.querySelectorAll('#rating-filter input[type="checkbox"]');

    function applyRatingFilter() {
        const selected = Array.from(ratingFilterInputs)
            .filter(input => input.checked && input.value !== "all")
            .map(input => input.value);

        const isAllChecked = document.querySelector('#rating-filter input[value="all"]').checked;

        let visibleCount = 0;
        productRows.forEach(row => {
            const ratingCell = row.querySelector("td[data-rating]");
            const ratingAttr = ratingCell?.getAttribute("data-rating");
            const rating = parseFloat(ratingAttr);
            const isRated = !isNaN(rating);
            let visible = false;

            if (isAllChecked) {
                visible = true;
            } else if (selected.includes("unrated") && !isRated) {
                visible = true;
            } else if (isRated) {
                for (const value of selected) {
                    const range = ratingRanges[value];
                    if (range && rating >= range.min && rating <= range.max) {
                        visible = true;
                        break;
                    }
                }
            }

            row.style.display = visible ? "" : "none";

            if (visible) {
                row.style.backgroundColor = (visibleCount % 2 === 0) ? '#d4d4d4' : '#ebebeb';
                visibleCount++;
            } else {
                row.style.backgroundColor = "";
            }
        });
    }

    // 'All' checkbox logic
    document.querySelector('#rating-filter input[value="all"]').addEventListener("change", function () {
        if (this.checked) {
            ratingFilterInputs.forEach(input => {
                if (input.value !== "all") input.checked = false;
            });
        }
        applyRatingFilter();
    });

    // Other checkboxes logic
    ratingFilterInputs.forEach(input => {
        if (input.value !== "all") {
            input.addEventListener("change", function () {
                if (this.checked) {
                    document.querySelector('#rating-filter input[value="all"]').checked = false;
                }
                const anyChecked = Array.from(ratingFilterInputs)
                    .some(cb => cb.checked && cb.value !== "all");
                if (!anyChecked) {
                    document.querySelector('#rating-filter input[value="all"]').checked = true;
                }
                applyRatingFilter();
            });
        }
    });

    applyRatingFilter(); // Initial run
});
</script>


    <script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const filterContainer = document.getElementById("color-filter");
    const allCheckbox = document.getElementById("color-all");
    const colorSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    if (!table || !filterContainer) return;

    const tableRows = Array.from(table.querySelectorAll("tbody tr"));

    // Collect unique colors (case-insensitive)
    tableRows.forEach(row => {
        const color = row.querySelector("button.add-to-builder")?.dataset.color || "Unknown";
        colorSet.add(color.trim().toLowerCase());
    });

    const colors = Array.from(colorSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes for each color
    colors.forEach(color => {
        const label = document.createElement("label");
        const displayName = color.charAt(0).toUpperCase() + color.slice(1); // Capitalize the first letter
        label.innerHTML = `<input type="checkbox" name="color" value="${color}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    // Append checkboxes to the filter container
    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none'; // Hide checkboxes beyond the visible count
        filterContainer.appendChild(el);
    });

    // Show more/less toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Function to apply color filter to the table
    function applyColorFilter() {
        const selectedColors = Array.from(document.querySelectorAll("input[name='color']:checked")).map(cb => cb.value);

        tableRows.forEach(row => {
            const color = row.querySelector("button.add-to-builder")?.dataset.color.trim().toLowerCase();
            row.style.display = selectedColors.includes(color) ? "" : "none";
        });

        // Update the state of the 'All' checkbox
        const allChecked = document.querySelectorAll("input[name='color']:checked").length === document.querySelectorAll("input[name='color']").length;
        allCheckbox.checked = allChecked;

        // Apply zebra striping after the filter is applied
        applyZebraStriping();
    }

    // Zebra striping logic
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";  // Alternate colors
        });
    }

    // 'All' checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='color']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyColorFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "color") {
            applyColorFilter();
        }
    });

    // Show more/less functionality for the checkboxes
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyColorFilter();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("boostclock-slider");
    const minLabel = document.getElementById("boostclock-min-label");
    const maxLabel = document.getElementById("boostclock-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));

    // Extract boost clock values from the table (adjusted for your table structure)
    const boostClockValues = rows.map(row => {
        const boostClockText = row.querySelector("td:nth-child(5)")?.textContent.trim() || "0 MHz";
        const match = boostClockText.match(/([\d.]+)\s?(MHz|GHz)/);
        if (!match) return 0;
        let value = parseFloat(match[1]);
        const unit = match[2];
        return (unit === "GHz") ? value * 1000 : value; // Convert GHz to MHz
    });

    const minBoostClock = Math.floor(Math.min(...boostClockValues));
    const maxBoostClock = Math.ceil(Math.max(...boostClockValues));
    let currentMin = minBoostClock;
    let currentMax = maxBoostClock;

    minLabel.textContent = `${minBoostClock} MHz`;
    maxLabel.textContent = `${maxBoostClock} MHz`;

    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-boostclock" min="${minBoostClock}" max="${maxBoostClock}" value="${minBoostClock}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-boostclock" min="${minBoostClock}" max="${maxBoostClock}" value="${maxBoostClock}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-boostclock");
    const maxSlider = document.getElementById("max-boostclock");

    function applyBoostClockFilter() {
        const minVal = parseInt(minSlider.value, 10);
        const maxVal = parseInt(maxSlider.value, 10);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `${minVal} MHz`;
        maxLabel.textContent = `${maxVal} MHz`;

        rows.forEach(row => {
            const boostClockText = row.querySelector("td:nth-child(5)")?.textContent.trim() || "0 MHz";
            const match = boostClockText.match(/([\d.]+)\s?(MHz|GHz)/);
            if (!match) {
                row.style.display = "none";
                return;
            }
            let value = parseFloat(match[1]);
            const unit = match[2];
            const boostClock = (unit === "GHz") ? value * 1000 : value;

            row.style.display = (boostClock >= minVal && boostClock <= maxVal) ? "" : "none";
        });

        // Zebra striping for visible rows
        const visibleRows = rows.filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    minSlider.addEventListener("input", () => {
        if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        applyBoostClockFilter();
    });

    maxSlider.addEventListener("input", () => {
        if (parseInt(maxSlider.value) < parseInt(minSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        applyBoostClockFilter();
    });

    applyBoostClockFilter();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("coreclock-slider");
    const minLabel = document.getElementById("coreclock-min-label");
    const maxLabel = document.getElementById("coreclock-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));

    // Extract core clock values from the table (adjusted for your table structure)
    const coreClockValues = rows.map(row => {
        const coreClockText = row.querySelector("td:nth-child(4)")?.textContent.trim() || "0 MHz";
        const match = coreClockText.match(/([\d.]+)\s?(MHz|GHz)/);
        if (!match) return 0;
        let value = parseFloat(match[1]);
        const unit = match[2];
        return (unit === "GHz") ? value * 1000 : value; // Convert GHz to MHz
    });

    const minCoreClock = Math.floor(Math.min(...coreClockValues));
    const maxCoreClock = Math.ceil(Math.max(...coreClockValues));
    let currentMin = minCoreClock;
    let currentMax = maxCoreClock;

    minLabel.textContent = `${minCoreClock} MHz`;
    maxLabel.textContent = `${maxCoreClock} MHz`;

    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-coreclock" min="${minCoreClock}" max="${maxCoreClock}" value="${minCoreClock}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-coreclock" min="${minCoreClock}" max="${maxCoreClock}" value="${maxCoreClock}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-coreclock");
    const maxSlider = document.getElementById("max-coreclock");

    function applyCoreClockFilter() {
        const minVal = parseInt(minSlider.value, 10);
        const maxVal = parseInt(maxSlider.value, 10);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `${minVal} MHz`;
        maxLabel.textContent = `${maxVal} MHz`;

        rows.forEach(row => {
            const coreClockText = row.querySelector("td:nth-child(4)")?.textContent.trim() || "0 MHz";
            const match = coreClockText.match(/([\d.]+)\s?(MHz|GHz)/);
            if (!match) {
                row.style.display = "none";
                return;
            }
            let value = parseFloat(match[1]);
            const unit = match[2];
            const coreClock = (unit === "GHz") ? value * 1000 : value;

            row.style.display = (coreClock >= minVal && coreClock <= maxVal) ? "" : "none";
        });

        // Zebra striping for visible rows
        const visibleRows = rows.filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    minSlider.addEventListener("input", () => {
        if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        applyCoreClockFilter();
    });

    maxSlider.addEventListener("input", () => {
        if (parseInt(maxSlider.value) < parseInt(minSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        applyCoreClockFilter();
    });

    applyCoreClockFilter();
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("memory-slider");
    const minLabel = document.getElementById("memory-min-label");
    const maxLabel = document.getElementById("memory-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));

    // Extract memory values from 2nd column (adjusted for your table structure)
    const memoryValues = rows.map(row => {
        const memoryText = row.querySelector("td:nth-child(3)")?.textContent.toUpperCase().trim() || "0 GB";
        const match = memoryText.match(/([\d.]+)\s?(GB)/);
        if (!match) return 0;
        return parseFloat(match[1]);
    });

    const minMemory = Math.floor(Math.min(...memoryValues));
    const maxMemory = Math.ceil(Math.max(...memoryValues));
    let currentMin = minMemory;
    let currentMax = maxMemory;

    minLabel.textContent = `${minMemory} GB`;
    maxLabel.textContent = `${maxMemory} GB`;

    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-memory" min="${minMemory}" max="${maxMemory}" value="${minMemory}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-memory" min="${minMemory}" max="${maxMemory}" value="${maxMemory}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-memory");
    const maxSlider = document.getElementById("max-memory");

    function applyMemoryFilter() {
        const minVal = parseInt(minSlider.value, 10);
        const maxVal = parseInt(maxSlider.value, 10);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `${minVal} GB`;
        maxLabel.textContent = `${maxVal} GB`;

        rows.forEach(row => {
            const memoryText = row.querySelector("td:nth-child(3)")?.textContent.toUpperCase().trim() || "0 GB";
            const match = memoryText.match(/([\d.]+)\s?(GB)/);
            if (!match) {
                row.style.display = "none";
                return;
            }
            const memory = parseFloat(match[1]);

            row.style.display = (memory >= minVal && memory <= maxVal) ? "" : "none";
        });

        // Zebra striping for visible rows
        const visibleRows = rows.filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    minSlider.addEventListener("input", () => {
        if (parseInt(minSlider.value) > parseInt(maxSlider.value)) {
            minSlider.value = maxSlider.value;
        }
        applyMemoryFilter();
    });

    maxSlider.addEventListener("input", () => {
        if (parseInt(maxSlider.value) < parseInt(minSlider.value)) {
            maxSlider.value = minSlider.value;
        }
        applyMemoryFilter();
    });

    applyMemoryFilter();
});
</script>

<script>
// Chipset Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("chipset-filter");
    const allCheckbox = document.getElementById("chipset-all");
    const chipsetSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique chipsets (case-insensitive)
    tableRows.forEach(row => {
        const chipset = row.querySelector("button.add-to-builder")?.dataset.chipset || "Unknown";
        chipsetSet.add(chipset.trim().toLowerCase());
    });

    const chipsets = Array.from(chipsetSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    chipsets.forEach(chipset => {
        const label = document.createElement("label");
        const displayName = chipset.charAt(0).toUpperCase() + chipset.slice(1);
        label.innerHTML = `<input type="checkbox" name="chipset" value="${chipset}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='chipset']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyChipsetFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='chipset']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const chipset = row.querySelector("button.add-to-builder")?.dataset.chipset.trim().toLowerCase();
            row.style.display = selected.includes(chipset) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='chipset']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyChipsetFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "chipset") {
            applyChipsetFilter();
        }
    });

    // Show more/less toggle
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyChipsetFilter();
});
</script>


<script>
// Manufacturer Filtering for Storage Page
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const tableRows = table.querySelectorAll("tbody tr");
    const filterContainer = document.getElementById("manufacturer-filter");
    const allCheckbox = document.getElementById("manufacturer-all");
    const manufacturerSet = new Set();
    const VISIBLE_COUNT = 4;
    let expanded = false;

    // Collect unique manufacturers (case-insensitive)
    tableRows.forEach(row => {
        const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer || "Unknown";
        manufacturerSet.add(manufacturer.trim().toLowerCase());
    });

    const manufacturers = Array.from(manufacturerSet).sort();
    const checkboxElements = [];

    // Create and append checkboxes
    manufacturers.forEach(manufacturer => {
        const label = document.createElement("label");
        const displayName = manufacturer.charAt(0).toUpperCase() + manufacturer.slice(1);
        label.innerHTML = `<input type="checkbox" name="manufacturer" value="${manufacturer}" checked> ${displayName}`;
        label.style.display = 'block';
        checkboxElements.push(label);
    });

    checkboxElements.forEach((el, index) => {
        if (index >= VISIBLE_COUNT) el.style.display = 'none';
        filterContainer.appendChild(el);
    });

    // Toggle link
    const toggleLink = document.createElement("a");
    toggleLink.href = "#";
    toggleLink.textContent = "Show more";
    toggleLink.style.display = checkboxElements.length > VISIBLE_COUNT ? "inline-block" : "none";
    toggleLink.style.marginTop = "5px";
    toggleLink.style.fontSize = "14px";
    toggleLink.style.color = "#0066cc";
    filterContainer.appendChild(toggleLink);

    // Zebra striping
    function applyZebraStriping() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function updateAllCheckboxState() {
        const allBoxes = Array.from(document.querySelectorAll("input[name='manufacturer']"));
        const checkedBoxes = allBoxes.filter(cb => cb.checked);
        allCheckbox.checked = checkedBoxes.length === allBoxes.length;
    }

    function applyManufacturerFilter() {
        const selected = Array.from(document.querySelectorAll("input[name='manufacturer']:checked"))
            .map(cb => cb.value);

        tableRows.forEach(row => {
            const manufacturer = row.querySelector("button.add-to-builder")?.dataset.manufacturer.trim().toLowerCase();
            row.style.display = selected.includes(manufacturer) ? "" : "none";
        });

        updateAllCheckboxState();
        applyZebraStriping();
    }

    // All checkbox logic
    allCheckbox.addEventListener("change", function () {
        const allBoxes = document.querySelectorAll("input[name='manufacturer']");
        allBoxes.forEach(cb => cb.checked = allCheckbox.checked);
        applyManufacturerFilter();
    });

    // Individual checkbox logic
    filterContainer.addEventListener("change", function (e) {
        if (e.target.name === "manufacturer") {
            applyManufacturerFilter();
        }
    });

    // Show more/less toggle
    toggleLink.addEventListener("click", function (e) {
        e.preventDefault();
        expanded = !expanded;
        checkboxElements.forEach((el, index) => {
            if (index >= VISIBLE_COUNT) el.style.display = expanded ? "block" : "none";
        });
        toggleLink.textContent = expanded ? "Show less" : "Show more";
    });

    // Initial filter application
    applyManufacturerFilter();
});
</script>

    <script>
// Price Filtering
document.addEventListener("DOMContentLoaded", function () {
    const table = document.getElementById("pcbuild-table");
    const sliderContainer = document.getElementById("price-slider");
    const minLabel = document.getElementById("price-min-label");
    const maxLabel = document.getElementById("price-max-label");

    if (!table || !sliderContainer) return;

    const rows = Array.from(table.querySelectorAll("tbody tr"));
    const prices = rows.map(row => {
        // Assuming price is in the 8th column (index 8)
        const priceText = row.querySelector("td:nth-child(8)")?.textContent.replace(/[^0-9.]/g, '') || "0";
        return parseFloat(priceText) || 0;
    });

    const minPrice = Math.floor(Math.min(...prices));
    const maxPrice = Math.ceil(Math.max(...prices));
    let currentMin = minPrice;
    let currentMax = maxPrice;

    // Set default labels
    minLabel.textContent = `$${minPrice}`;
    maxLabel.textContent = `$${maxPrice}`;

    // Create 2 sliders
    sliderContainer.innerHTML = `
        <input type="range" class="min-range-bg" id="min-price" min="${minPrice}" max="${maxPrice}" value="${minPrice}" step="1" style="width: 100%;">
        <input type="range" class="max-range-bg" id="max-price" min="${minPrice}" max="${maxPrice}" value="${maxPrice}" step="1" style="width: 100%; margin-top: 10px;">
    `;

    const minSlider = document.getElementById("min-price");
    const maxSlider = document.getElementById("max-price");

    function applyZebraStripes() {
        const visibleRows = Array.from(table.querySelectorAll("tbody tr")).filter(row => row.style.display !== "none");
        visibleRows.forEach((row, index) => {
            row.style.backgroundColor = (index % 2 === 0) ? "#d4d4d4" : "#ebebeb";
        });
    }

    function filterByPrice() {
        const minVal = parseFloat(minSlider.value);
        const maxVal = parseFloat(maxSlider.value);
        currentMin = minVal;
        currentMax = maxVal;

        minLabel.textContent = `$${minVal}`;
        maxLabel.textContent = `$${maxVal}`;

        rows.forEach(row => {
            const priceText = row.querySelector("td:nth-child(8)")?.textContent.replace(/[^0-9.]/g, '') || "0";
            const price = parseFloat(priceText) || 0;

            row.style.display = (price >= minVal && price <= maxVal) ? "" : "none";
        });

        applyZebraStripes();
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

    // Initial filter apply
    filterByPrice();
});
</script>

<script>
// Sorting Logic
document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById("pcbuild-table");
    const headers = table.querySelectorAll(".sortable-header");

    let currentSort = { key: null, direction: 'asc' };

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const key = header.dataset.key;
            currentSort.direction = (currentSort.key === key && currentSort.direction === 'asc') ? 'desc' : 'asc';
            currentSort.key = key;

            headers.forEach(h => {
                h.innerHTML = `▶ ${h.textContent.trim().replace(/^▲|▼|▶/, '')}`;
            });

            header.innerHTML = `${currentSort.direction === 'asc' ? '▲' : '▼'} ${header.textContent.trim().replace(/^▲|▼|▶/, '')}`;

            sortTableByKey(key, currentSort.direction);
        });
    });

    function sortTableByKey(key, direction) {
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        rows.sort((a, b) => {
            const getValue = (row, key) => {
                const index = getColumnIndex(key);
                const cell = row.querySelector(`td:nth-child(${index})`);
                if (!cell) return '';

                if (key === 'rating') {
                    return parseFloat(cell.dataset.rating || '0');
                }

                if (['price', 'core', 'boost'].includes(key)) {
                    const num = parseFloat(cell.textContent.replace(/[^0-9.]/g, ''));
                    return isNaN(num) ? 0 : num;
                }

                return cell.textContent.trim().toLowerCase();
            };

            const valA = getValue(a, key);
            const valB = getValue(b, key);

            if (typeof valA === 'number' && typeof valB === 'number') {
                return direction === 'asc' ? valA - valB : valB - valA;
            }

            return direction === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
        });

        rows.forEach((row, i) => {
            row.style.backgroundColor = (i % 2 === 0) ? '#d4d4d4' : '#ebebeb';
            tbody.appendChild(row);
        });
    }

    function getColumnIndex(key) {
        const mapping = {
            name: 1,
            chipset: 2,
            memory: 3,
            core: 4,
            boost: 5,
            color: 6,
            rating: 7,
            price: 8
        };
        return mapping[key];
    }
});
</script>


    <?php
	include('parts-footer.php');
    return ob_get_clean();
}
add_shortcode('pcbuild_parts_gpu', 'aawp_pcbuild_display_parts_gpu');

