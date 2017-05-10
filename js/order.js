(function ($) {
    $(document).ready(function(){

        /*
        *    Gets the ID of the menu item that was clicked (should be
        *    menu-quantity-item, or its child).
        */
        function getClickedItemID(target) {
            var currentElement = $(target);
            while (!currentElement.is("div.menu-item-quantity") && !currentElement.is("body")) {
                currentElement = currentElement.parent();
            }
            var id = currentElement.attr("id");
            if (id.startsWith("item-quantity-")) {
                var parts = id.split("-");
                return parts[parts.length - 1];
            }
            return -1;
        }

        /*
        *    Updates the data being shown in the view to reflect the data
        *    stored in the model
        */
        function updateView() {
            $("input.menu-item-quantity-text").each(function(i, elem) {
                var id = getClickedItemID(elem);
                var quantity = cart.get(id);
                if (!quantity) {
                    quantity = 0;
                }
                $(elem).val(quantity);
            });
        }

        function refreshMenuView() {
            if (!arraysSame(menu, oldMenu)) {
                $("#menu-list").empty();
                menu.each(function(item) {
                    item.addAsMenuItem();
                });

                // Register the button event handlers
                $("div.menu-item-quantity .menu-subtract-button").click(subtractHandler);
                $("div.menu-item-quantity .menu-add-button").click(addHandler);

                // Refresh the quantities
                updateView();
            }
        }

        /*
        *     Event handler for add buttons
        */
        function addHandler(e) {
            var id = getClickedItemID(e.target);

            // Add to model
            cart.add(id);

            // Refresh View
            updateView();
            updateCart();
        }

        /*
        *     Event handler for subtract buttons
        */
        function subtractHandler(e) {
            var id = getClickedItemID(e.target);

            // Add to model
            cart.remove(id);

            // Refresh View
            updateView();
            updateCart();
        }

        /*
        *     Event handler for subtract buttons
        */
        $("#menu-categories .category-link").click(function(e){
            var target = e.target;
            // Don't bother if this is already the current category
            if (!$(target).hasClass("category-selected")) {
                // Show the user that they've selected this category
                $("#menu-categories .category-link").removeClass("category-selected");
                $(target).addClass("category-selected");

                // Get the id of the category thats been selected
                currentCategory = $(target).attr("data-cat-id");

                // Update the menu
                getMenu(currentCategory, refreshMenuView);
            }
        });

        var mobile = window.matchMedia("screen and (max-device-width: 450px)");
        if (mobile.matches) {
            console.log("is mobile phone");
            $("#menu-categories h2").on("click", function(e) {

                $("#menu-categories p").slideToggle();
            })
        }

        // The category that's currently being displayed in the menu
        var currentCategory = 0; // category 0 = 'All Items'

        // Get the cart saved in the session
        getCart(updateView);

        // Get the menu
        getMenu(currentCategory, refreshMenuView);
    });
})(jQuery);