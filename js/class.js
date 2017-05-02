var Cart = Class.create({
    initialize: function() {
        this.items = new $H();
    },

    add: function(itemID, change = 1) {
        // If it's already in the item set, add the quantity
        var current = this.items.get(itemID);
        if (current) {
            this.items.set(itemID, current + change);
        } else {
            // Not in item set yet
            this.items.set(itemID, change);
        }
    },

    remove: function (itemID, change = 1) {
        // If it's already in the item set, sub the quantity
        var current = this.items.get(itemID);
        if (current) {
            var newQuantity = current - change;
            if (newQuantity <= 0) {
                this.items.unset(itemID);
            } else {
                this.items.set(itemID, newQuantity);
            }
        }
    },

    removeAll: function (itemID) {
        this.items.unset(itemID);
    },

    update: function(itemID, quantity) {
        if (quantity < 1) {
            this.removeAll(itemID);
            return;
        }
        this.items.set(itemID, quantity);
    },

    get: function(itemID) {
        var quantity = this.items.get(itemID);
        return quantity;
    },
    has: function(itemID) {
        return this.items.keys().indexOf(itemID) >= 0;
    },
});

var Item = Class.create({
    initialize: function(itemID, name, desc, stock, price, image, categories) {
        this.itemID = itemID;
        this.name = name;
        this.desc = desc;
        this.stock = stock;
        this.price = price;
        this.image = image;
        this.categories = categories;
    },

    /*
    *    Puts a Menu Item section for this item into the menu list
    */
    addAsMenuItem: function() {
        var itemHTML = "<div class=\"menu-item\"\n>";
        itemHTML += "<img src=\"" + this.image + "\" class=\"menu-item-thumb\" />\n";
        itemHTML += "<table class=\"menu-item-content\">\n";
        itemHTML += "<tbody>\n<tr>\n<td>\n";
        itemHTML += "<a href=\"/order/item.php?itemID=" + this.itemID + "\" class=\"menu-item-title\">" + this.name + "</a>\n";
        itemHTML += "</td>\n<td>\n";
        itemHTML += "<p class=\"menu-item-price\">\$" + this.price + "</p>\n";
        itemHTML += "</td>\n</tr>\n<tr>\n<td>\n";
        itemHTML += "<p class=\"menu-item-desc\">" + this.desc +"</p>\n";
        itemHTML += "</td>\n<td>\n";
        itemHTML += "<div class=\"menu-item-quantity\" id=\"item-quantity-" + this.itemID + "\">\n";
        itemHTML += "<a class=\"menu-subtract-button\"><div>-</div></a>\n";
        itemHTML += "<input type=\"text\" class=\"menu-item-quantity-text\" disabled value=\"\" />\n";
        itemHTML += "<a class=\"menu-add-button\"><div>+</div></a>\n";
        itemHTML += "</div>\n</td>\n</tr>\n</tbody>\n</table>\n</div>\n";
        jQuery("#menu-list").append(itemHTML);
    }
});

function arraysSame(a, b) {
    if (a == b) return true;
    if (a == null || b == null) return true;
    if (a.length != b.length) return false;
    var c = $A(a);
    var d = $A(b);
    c.each(function (elem, i) {
        if (elem != d[i]) {
            return false;
        }
    });
    return true;
}

/*
*    Parses a JSON object and returns the array of menu items it
*    represents
*/
function jsonToMenu(json) {
    var itemsOut = new Array();

    json.items.each(function(i) {
        var item = new Item(i.itemID, i.name, i.description, i.availability, i.price, i.image, i.categories);
        itemsOut.push(item);
    });

    return itemsOut;
}

/*
*     Parses a JSON object and returns the cart object it represents
*/
function jsonToCart(json) {
    var newCart = new Cart();
    if (json && json.hasOwnProperty("items")) {
        var items = json["items"];
        for (var item in items) {
            newCart.add(item, items[item]);
        }
    }
    return newCart;
}

/*
*    Gets the cart stored in the PHP session
*    Parses the json returned in the request and puts it in the cart
*    variable
*/
function getCart(callback = null) {
    new Ajax.Request("/data/getCart.php", {
        method: "get",
        onSuccess: function (resp) {
            var json = resp.responseJSON;
            if (json) {
                cart = jsonToCart(json);
            } else {
                // Something went wrong
                console.log("Something went wrong while updating cart... Response was:");
                console.log(resp.responseText);
            }
            if (callback) {
                callback();
            }
        }
    });
}

/*
*    Lets the server know about changes/updates to the cart model
*/
function updateCart(callback = null) {
    console.log("JSON", Object.toJSON(cart));

    // Tell server what's in our cart
    new Ajax.Request('/data/updateCart.php', {
        method: "post",
        parameters: {
            cartState: Object.toJSON(cart)
        },
        onSuccess: function (resp) {
            // Check that the cart was updated successfully
            var json = resp.responseJSON;
            if (json) {
                if (json.hasOwnProperty("success") && json["success"]) {
                    // It was all good
                } else {
                    console.log(json);
                }
            } else {
                console.log(resp.responseText);
            }
            if (callback) {
                callback();
            }
        }
    });
}

/*
*    Gets the menu (that is, the set of items in the selected category)
*/
function getMenu(category, callback = null) {
    new Ajax.Request("/data/getItem.php", {
        method: "get",
        parameters: {
            categoryID: category,
        },
        onSuccess: function (resp) {
            if (resp.responseJSON) {
                oldMenu = menu; // backup old menu
                menu = jsonToMenu(resp.responseJSON);
            } else {
                console.log("Failure with AJAX request to getItems");
                console.log(resp);
            }
            if (callback) {
                callback();
            }
        }
    });
}

// Global variables
// -- Empty cart and menu to start with
var cart = new Cart();
var menu = new Array();
var oldMenu = menu;