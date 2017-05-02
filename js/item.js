(function ($) {
    var itemID;

    function refreshQuantityText() {
        // Get the current cart
        getCart(function(){
            // Get the current value form the cart
            var value = "0";
            if (cart.has(itemID)) {
                value = cart.get(itemID);
            }

            // Update what's being shown in the text
            $("#itemQuantity").val(value);

            // Focus on the quantity to start (so user knows it's an input)
            $("#itemQuantity").focus();
        });
    }

    function verifyQuantityText() {
        return validPositiveInt($("#itemQuantity").val());
    }

    function validPositiveInt(value) {
        // Check if it contains letters
        return !value.match("[^0-9]")
    }

    function goodUpdate() {
        $("#itemQuantity").addClass("updated").attr("disabled", "true");
        setTimeout(function(){
            window.location.href = "/order/index.php";
        }, 1000);
    }

    $(document).ready(function(){
        itemID = $("#item-info").attr("data-item-id");
        refreshQuantityText();

        $("#itemQuantity").change(function(){
            // Trim the text in quantity - get rid accidental of whitespace
            // that will cause false negatives in validation
            $("#itemQuantity").val($("#itemQuantity").val().trim());

            // Check if it's valid (i.e. positive int)
            if (verifyQuantityText()) {
                $("#itemQuantity").removeClass("error");
            } else {
                $("#itemQuantity").addClass("error");
            }
        })

        $("#updateOrder").click(function(e){
            if (verifyQuantityText()) {
                cart.update(itemID, $("#itemQuantity").val());
                updateCart(goodUpdate);
            }
        });

        $("#removeFromOrder").click(function(e){
            cart.removeAll(itemID);
            $("#itemQuantity").val("0");
            updateCart(goodUpdate);
        });
    });
})(jQuery);