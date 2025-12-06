
<div id="price-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden opacity-0">
    <div class="modal-container bg-white w-full max-w-md rounded-lg shadow-xl transform -translate-y-10">
        <form action="viewacceptedreqdetails.php?id=<?php echo $request_id; ?>" method="POST">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Set Stitching Price</h2>
                    <button id="close-price-modal-btn" type="button" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
                </div>
                <p class="text-sm text-gray-600 mt-2">Enter the final price for this request. The customer will be notified to make the payment.</p>
                <input type="hidden" name="action" value="set_price">
                <input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>">
                <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($request_details['customer_email']); ?>">
                <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($request_details['cname']); ?>">
                <input type="hidden" name="request_name" value="<?php echo htmlspecialchars($request_details['sdname']); ?>">
                <div class="mt-4">
                    <label for="sprice" class="block text-sm font-medium text-gray-700">Price (₹)</label>
                    <input type="number" id="sprice" name="sprice" step="0.01" min="1" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., 1500.00" required>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                <button type="button" id="cancel-price-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">Set Price</button>
            </div>
        </form>
    </div>
</div>

<div id="email-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden opacity-0">
     <div class="modal-container bg-white w-full max-w-lg rounded-lg shadow-xl transform -translate-y-10">
        <form action="viewacceptedreqdetails.php?id=<?php echo $request_id; ?>" method="POST">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Send Email to Customer</h2>
                    <button id="close-email-modal-btn" type="button" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
                </div>
                <p class="text-sm text-gray-600 mt-2">Compose your message to <?php echo htmlspecialchars($request_details['cname']); ?>.</p>
                <div class="mt-4 space-y-4">
                    <input type="hidden" name="action" value="send_email">
                    <input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>">
                    <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($request_details['customer_email']); ?>">
                    <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($request_details['cname']); ?>">
                    <input type="hidden" name="request_name" value="<?php echo htmlspecialchars($request_details['sdname']); ?>">
                    <div>
                        <label for="email_subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" id="email_subject" name="email_subject" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" value="Regarding your Stitching Request #<?php echo htmlspecialchars($request_details['sdid']); ?>" required>
                    </div>
                    <div>
                        <label for="email_message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea id="email_message" name="email_message" rows="6" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="e.g., Hello, your order is progressing well and will be ready by..." required></textarea>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                <button type="button" id="cancel-email-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700">Send Email</button>
            </div>
        </form>
    </div>
</div>

<div id="cancel-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden opacity-0">
    <div class="modal-container bg-white w-full max-w-md rounded-lg shadow-xl transform -translate-y-10">
        <form action="viewacceptedreqdetails.php?id=<?php echo $request_id; ?>" method="POST">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Cancel Request</h2>
                    <button id="close-cancel-modal-btn" type="button" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
                </div>
                <p class="text-sm text-gray-600 mt-2">Provide a reason for cancelling this request. This will be sent to the customer.</p>
                <input type="hidden" name="action" value="cancel_request">
                <input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>">
                <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($request_details['customer_email']); ?>">
                <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($request_details['cname']); ?>">
                <input type="hidden" name="request_name" value="<?php echo htmlspecialchars($request_details['sdname']); ?>">
                <div class="mt-4">
                    <label for="cancellation_reason" class="sr-only">Reason for Cancellation</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="e.g., Unable to source the required material, unforeseen circumstances, etc." required></textarea>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-lg">
                <button type="button" id="cancel-cancel-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Back</button>
                <button type="submit" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

<div id="shipping-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 opacity-0">
    <div class="modal-container bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto p-8 transform -translate-y-10">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-gray-800">Send Shipping Details</h3>
            <button id="close-shipping-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
        </div>
        <p class="text-gray-600 mb-6">Enter the shipping information for request #<?php echo htmlspecialchars($request_details['sdid']); ?>. An email will be sent to the customer.</p>
        
        <form action="viewacceptedreqdetails.php?id=<?php echo $request_id; ?>" method="POST">
            <input type="hidden" name="action" value="send_shipping_info_custom">
            <input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>">
            <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($request_details['customer_email']); ?>">
            <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($request_details['cname']); ?>">
            <input type="hidden" name="request_name" value="<?php echo htmlspecialchars($request_details['sdname']); ?>">

            <div class="mb-4">
                <label for="shipping_provider" class="block text-sm font-medium text-gray-700 mb-1">Shipping Provider *</label>
                <input type="text" name="shipping_provider" id="shipping_provider" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g., FedEx, UPS, Delhivery">
            </div>

            <div class="mb-6">
                <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-1">Tracking Number *</label>
                <input type="text" name="tracking_number" id="tracking_number" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g., 1Z999AA10123456784">
            </div>
            
            <div class="flex justify-end gap-4">
                <button type="button" id="cancel-shipping-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 flex items-center gap-2">
                    <i class="ri-send-plane-fill"></i> Send Email
                </button>
            </div>
        </form>
    </div>
</div>