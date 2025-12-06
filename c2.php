<?php
session_start();
include('databasecon.php');
$db = new DatabaseCon();
$val = $_SESSION['uid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Stitching Requests</title>
</head>

<body>
<tr>
    <td><a href="customerhome.php">Home</a></td>&nbsp;&nbsp;
    <td><a href="cupdate.php">My Profile</a></td>&nbsp;&nbsp;
    <td><a href="cviewt.php">View Tailors</a></td>&nbsp;&nbsp;
    <td><a href="viewdesigns.php">View Designs</a></td>&nbsp;&nbsp;
    <td><a href="meas.php">Upload Measurements</a></td>&nbsp;&nbsp;
    <td><a href="customreq1.php">Stitch Requesting</a></td>&nbsp;&nbsp;
    <td><a href="index.php">Logout</a></td>&nbsp;&nbsp;
</tr>
<center>
    <h1>Stitching Requests</h1>
    <form action="stitchreqaction.php" method="post" enctype="multipart/form-data">
    <table>
        <!-- Optional: Assign to a Specific Tailor -->
        <tr>
            <td>Send to</td>
            <td>
                <select name="tid">
                    <option value="">Any Available Tailor</option>
                    <?php
                    // Fetch active tailors
                    $tailorSQL = "SELECT tid, tname FROM treg ";
                    $tailorResult = $db->selectData($tailorSQL);
                    while ($trow = mysqli_fetch_array($tailorResult)) { ?>
                        <option value="<?php echo $trow['tid']; ?>"><?php echo $trow['tname']; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <!-- Dress Name -->
        <tr> 
            <td>Dress Name</td>
            <td><input type="text" name="sdname" required></td>
        </tr>
        <!-- Dress Type with Dropdown (Triggers Dependent Design Options) -->
        <tr>
            <td>Dress Type</td>
            <td>
                <select name="sdtype" id="sdtype">
                    <option value="">Select Dress Type</option>
                    <option value="Kurti">Kurti</option>
                    <option value="Suit">Suit</option>
                    <option value="Saree">Saree</option>
                    <option value="Lehenga">Lehenga</option>
                    <option value="Other">Other</option>
                </select>
            </td>
        </tr>
        <!-- Fabric Type with Dropdown -->
        <tr>
            <td>Fabric Type</td>
            <td>
                <select name="sfabric">
                    <option value="Cotton">Cotton</option>
                    <option value="Silk">Silk</option>
                    <option value="Linen">Linen</option>
                    <option value="Polyester">Polyester</option>
                    <option value="Other">Other</option>
                </select>
            </td>
        </tr>
        <!-- Color Selection -->
        <tr>
            <td>Preferred Color</td>
            <td>
                <select name="scolor">
                    <option value="Red">Red</option>
                    <option value="Blue">Blue</option>
                    <option value="Green">Green</option>
                    <option value="Black">Black</option>
                    <option value="White">White</option>
                    <option value="Other">Other</option>
                </select>
            </td>
        </tr>
        <!-- Pattern Selection with Optgroup for Clarity -->
        <tr>
            <td>Pattern</td>
            <td>
                <select name="spattern">
                    <optgroup label="Classic">
                        <option value="Solid">Solid</option>
                        <option value="Checked">Checked</option>
                    </optgroup>
                    <optgroup label="Modern">
                        <option value="Striped">Striped</option>
                        <option value="Printed">Printed</option>
                        <option value="Embroidered">Embroidered</option>
                    </optgroup>
                    <option value="Other">Other</option>
                </select>
            </td>
        </tr>
        <!-- Design Templates (Dependent Dropdowns) -->
        <tr>
            <td>Neck Design</td>
            <td>
                <select name="sneck" id="sneck">
                    <option value="">Select Neck Design</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Shoulder Style</td>
            <td>
                <select name="sshoulder" id="sshoulder">
                    <option value="">Select Shoulder Style</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Sleeve Style</td>
            <td>
                <select name="ssleeve" id="ssleeve">
                    <option value="">Select Sleeve Style</option>
                </select>
            </td>
        </tr>
        <!-- Special Instructions -->
        <tr>
            <td>Special Instructions</td>
            <td><input type="text" name="sinstructions"></td>
        </tr>
        <!-- Additional Customizations -->
        <tr>
            <td>Customization Requests</td>
            <td>
                <textarea name="scustom" rows="3" cols="30" placeholder="e.g., embroidery or embellishments"></textarea>
            </td>
        </tr>
        <!-- Priority Selection -->
        <tr>
            <td>Priority</td>
            <td>
                <input type="radio" name="spriority" value="Standard" checked> Standard 
                <input type="radio" name="spriority" value="Expedited"> Expedited
            </td>
        </tr>
        <!-- Delivery Date -->
        <tr>
            <td>Delivery Date</td>
            <td><input type="date" name="sddate"></td>
        </tr>
        <!-- Multiple Image Upload -->
        <tr>
            <td>Reference Images</td>
            <td><input type="file" name="file[]" multiple></td>
        </tr>
        <!-- Price Estimator (Read-only - updated via JavaScript) -->
        <tr>
            <td>Estimated Price (₹)</td>
            <td><input type="text" name="sprice" id="price" readonly></td>
        </tr>
        <!-- Additional Comments (Communication Feature) -->
        <tr>
            <td>Additional Comments</td>
            <td>
                <textarea name="scomments" rows="3" cols="30" placeholder="Any questions or extra details"></textarea>
            </td>
        </tr>
        <!-- Submit Button -->
        <tr>
            <td></td>
            <td><input type="submit" value="Submit Request"></td>
        </tr>
    </table>
    </form>

    <!-- User History & Repeat Orders Link -->
    <br>
    <a href="vc.php">View Order History & Repeat Orders</a>
    
    
    
    <!-- Simple Price Estimation Script -->
    <script>
        // Dummy price calculator: base price is 1000₹, add 500₹ for expedited priority,
        // add additional cost based on fabric type selection (Silk adds 300₹, Linen adds 200₹).
        function calculatePrice() {
            var basePrice = 1000;
            var priority = document.querySelector('input[name="spriority"]:checked').value;
            var fabric = document.getElementsByName('sfabric')[0].value;
            var extra = 0;
            if(priority === "Expedited") {
                extra += 500;
            }
            if(fabric === "Silk") {
                extra += 300;
            } else if(fabric === "Linen") {
                extra += 200;
            }
            var total = basePrice + extra;
            document.getElementById('price').value = total;
        }

        // Attach change event handlers to update price
        document.getElementsByName('sfabric')[0].addEventListener('change', calculatePrice);
        var priorityRadios = document.getElementsByName('spriority');
        for(var i = 0; i < priorityRadios.length; i++){
            priorityRadios[i].addEventListener('change', calculatePrice);
        }
        calculatePrice();

        // Dynamic dependent dropdowns for design options based on dress type
        var designOptions = {
            "Kurti": {
                "neck": [
                    { value: "V-Neck", text: "V-Neck" },
                    { value: "Round Neck", text: "Round Neck" },
                    { value: "Square Neck", text: "Square Neck" },
                    { value: "Scoop Neck", text: "Scoop Neck" },
                    { value: "Other", text: "Other" }
                ],
                "shoulder": [
                    { value: "Drop Shoulder", text: "Drop Shoulder" },
                    { value: "Puffed Shoulders", text: "Puffed Shoulders" },
                    { value: "Other", text: "Other" }
                ],
                "sleeve": [
                    { value: "Short Sleeve", text: "Short Sleeve" },
                    { value: "Three Quarter Sleeve", text: "Three Quarter Sleeve" },
                    { value: "Sleeveless", text: "Sleeveless" },
                    { value: "Other", text: "Other" }
                ]
            },
            "Suit": {
                "neck": [
                    { value: "Collared", text: "Collared" },
                    { value: "Mandarin", text: "Mandarin" },
                    { value: "Notched", text: "Notched" },
                    { value: "Other", text: "Other" }
                ],
                "shoulder": [
                    { value: "Structured", text: "Structured" },
                    { value: "Unstructured", text: "Unstructured" },
                    { value: "Other", text: "Other" }
                ],
                "sleeve": [
                    { value: "Long Sleeve", text: "Long Sleeve" },
                    { value: "Half Sleeve", text: "Half Sleeve" },
                    { value: "Other", text: "Other" }
                ]
            },
            "Saree": {
                "neck": [
                    { value: "Blouse V-Neck", text: "Blouse V-Neck" },
                    { value: "Blouse Round", text: "Blouse Round" },
                    { value: "Other", text: "Other" }
                ],
                "shoulder": [
                    { value: "Classic", text: "Classic" },
                    { value: "Contoured", text: "Contoured" },
                    { value: "Other", text: "Other" }
                ],
                "sleeve": [
                    { value: "Short Sleeve", text: "Short Sleeve" },
                    { value: "Long Sleeve", text: "Long Sleeve" },
                    { value: "Sleeveless", text: "Sleeveless" },
                    { value: "Other", text: "Other" }
                ]
            },
            "Lehenga": {
                "neck": [
                    { value: "Off-Shoulder", text: "Off-Shoulder" },
                    { value: "Cowl Neck", text: "Cowl Neck" },
                    { value: "Other", text: "Other" }
                ],
                "shoulder": [
                    { value: "Strapless", text: "Strapless" },
                    { value: "Padded", text: "Padded" },
                    { value: "Other", text: "Other" }
                ],
                "sleeve": [
                    { value: "Cap Sleeve", text: "Cap Sleeve" },
                    { value: "Sleeveless", text: "Sleeveless" },
                    { value: "Other", text: "Other" }
                ]
            },
            "Other": {
                "neck": [
                    { value: "Standard", text: "Standard" },
                    { value: "Other", text: "Other" }
                ],
                "shoulder": [
                    { value: "Standard", text: "Standard" },
                    { value: "Other", text: "Other" }
                ],
                "sleeve": [
                    { value: "Standard", text: "Standard" },
                    { value: "Other", text: "Other" }
                ]
            }
        };

        function populateDesigns() {
            var dressType = document.getElementById('sdtype').value;
            // Get the design selects
            var neckSelect = document.getElementById('sneck');
            var shoulderSelect = document.getElementById('sshoulder');
            var sleeveSelect = document.getElementById('ssleeve');

            // Clear current options and add a default option
            neckSelect.innerHTML = '<option value="">Select Neck Design</option>';
            shoulderSelect.innerHTML = '<option value="">Select Shoulder Style</option>';
            sleeveSelect.innerHTML = '<option value="">Select Sleeve Style</option>';

            if(designOptions[dressType]) {
                // Populate Neck Designs
                designOptions[dressType].neck.forEach(function(opt) {
                    var el = document.createElement("option");
                    el.value = opt.value;
                    el.textContent = opt.text;
                    neckSelect.appendChild(el);
                });
                // Populate Shoulder Styles
                designOptions[dressType].shoulder.forEach(function(opt) {
                    var el = document.createElement("option");
                    el.value = opt.value;
                    el.textContent = opt.text;
                    shoulderSelect.appendChild(el);
                });
                // Populate Sleeve Styles
                designOptions[dressType].sleeve.forEach(function(opt) {
                    var el = document.createElement("option");
                    el.value = opt.value;
                    el.textContent = opt.text;
                    sleeveSelect.appendChild(el);
                });
            }
        }

        // Attach event listener for dress type change
        document.getElementById('sdtype').addEventListener('change', populateDesigns);
    </script>
</center>
<body>
</html>