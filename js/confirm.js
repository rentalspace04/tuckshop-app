(function($){

    function handleConfirmResponse(resp) {
        console.log(resp);
        if (resp.responseJSON) {
            var json = resp.responseJSON;
            if (json["success"]) {
                $("#order-message").show().removeClass("failure").addClass("success").text("Order successfully submitted! Redirecting to home...");
                // Order has been submitted successfully
                setTimeout(function(){
                    window.location.href = "/home.php";
                }, 2000);
            } else {
                $("#order-message").show().addClass("failure").text(
                    "Server Error: " + json["message"] + ". Maybe try submitting/making the order again."
                );
                console.log(json["message"]);
            }
        } else {
            console.log("Something went wrong with the AJAX request.");
            console.log(resp);
        }
    }

    function showInputError(input) {
        $(input).addClass("error");
    }

    function removeInputError(input) {
        $(input).removeClass("error");
    }

    function validateForm() {
        var result = true;
        var type = getUserType();
        var inputs = new Array();

        inputs.push("#paymentType");
        inputs.push("#pickupTime");

        if (getUserType() == 3) {
            // If the user is a parent
            inputs.push("#orderFor");
        }

        inputs.each(function(input){
            if ($(input + " option:selected").text() == "--") {
                showInputError(input);
                result = false;
            } else {
                removeInputError(input);
            }
        });

        return result;
    }

    function getUserType() {
        return $("#placeOrderForm").attr("data-user-type");
    }

    function getParameters() {
        if (getUserType() == 3) {
            // Parent Users
            return {
                paymentType: $("#paymentType option:selected").attr("data-payment-id"),
                pickupTime: $("#pickupTime option:selected").text(),
                orderFor: $("#orderFor option:selected").attr("data-child-id")
            };
        } else {
            // Student Users
            return {
                paymentType: $("#paymentType option:selected").attr("data-payment-id"),
                pickupTime: $("#pickupTime option:selected").text(),
            };
        }
    }

    $(document).ready(function(){
        $("select").on("change", validateForm).on("click", validateForm);

        $("#placeOrderButton").click(function(e){
            if (validateForm()) {
                parameters = getParameters();
                // Send an AJAX request to check if the order will be accepted
                new Ajax.Request("/data/confirmOrder.php", {
                    method: "post",
                    parameters: parameters,
                    onSuccess: handleConfirmResponse,
                });
            }
        });
    });
})(jQuery);