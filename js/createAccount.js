(function($){
    function clearSelectedTabs(elem) {
        $(elem).children("li").removeClass("selected");
    }

    function showForm(targetTab) {
        var newTabID = $(targetTab).attr("data-form-id");
        var currentTabID = getCurrentTabID();
        // Don't do transition if they've clicked on the same tab
        if (newTabID == currentTabID) {
            return;
        }
        var newHeight = $("#" + newTabID).height();
        // Change the height to fit the new form
        $("#createAccountFormContainer").animate({"height": newHeight}, 400);
        // Fade the old form out and the new one in
        $("#" + currentTabID).fadeOut(200, function(){
            $("form#" + newTabID).fadeIn(200);
        });

        // Transfer matching details over -
        // Transfer: first name, last name, email, phone, dob, password,
        //           confirm password
        $("#" + currentTabID + " input").each(function(i, elem) {
            transferFormData($(elem).attr("name"), currentTabID, newTabID);
        });
    }

    function getCurrentTabID() {
        return $(".tab-select > li.selected").attr("data-form-id");
    }

    function showErrorMessage(message, msgShown) {
        if (!msgShown) {
            $("#errorMessage").text(message).slideDown(100);
        }
    }

    function clearErrorMessage(){
        $("#errorMessage").slideUp(100);
    }

    function getInput(tabID, name) {
        return $("#" + tabID + " input[name=\"" + name + "\"]");
    }

    function isInteger(val) {
        return ("" + val).match("^[0-9]+$") != null;
    }

    function validateForm() {
        var currentTabID = getCurrentTabID();
        var valid = true;
        var msgShown = false;

        $("#" + currentTabID + " input.required").each(function (i, elem) {
            // Check that the required inputs have a value
            if ($(elem).val() == "") {
                $(elem).addClass("error");
                showErrorMessage("You're missing some required field(s).", msgShown);
                msgShown = true;
                valid = false;
            } else {
                $(elem).removeClass("error");
            }
        });

        if (!valid) {
            return false;
        }

        var pwd = getInput(currentTabID, "password");
        var cpwd = getInput(currentTabID, "confirmpassword");

        if ($(pwd).val() != $(cpwd).val()) {
            $(pwd).addClass("error");
            $(cpwd).addClass("error");
            showErrorMessage("Passwords don't match", msgShown);
            return false;
        }

        // Check that the year level is valid
        var yearlevel = getInput(currentTabID, "yearlevel").each(function(i, yl){
            var level = $(yl).val();
            if (!isInteger(level) || level <= 0 && level > 12) {
                valid = false;
                showErrorMessage("Invalid Year Level of '" + level + "'");
                $(yl).addClass("error");
            }
        });

        if (!valid) {
            return false;
        }

        // Check that the date is valid

        // Check that the email is unused
        // TODO

        if (valid) {
            clearErrorMessage();
        }

        return valid;
    }

    function transferFormData(name, from, to) {
        $("#" + to).find("input[name=\"" + name + "\"]").val(
            $("#" + from).find("input[name=\"" + name + "\"]").val()
        );
    }

    function trySubmitForm(e) {
        e.preventDefault();
        if (validateForm()) {
            console.log("Form valid. Beginning submission");
        } else {
            console.log("Invalid form data")
        }
        return false;
    }

    $(document).ready(function() {
        $(".tab-select > li").on("click", function(e){
            var target = e.target;
            showForm(target);
            clearSelectedTabs($(target).parent());
            $(target).addClass("selected");
        });

        $("form input.required").on("change", function(e){
            if ($(e.target).val() != "") {
                $(e.target).removeClass("error");
            }
        })

        $("#createAccountParent").submit(trySubmitForm);
        $("#createAccountStudent").submit(trySubmitForm);
    });
})(jQuery);