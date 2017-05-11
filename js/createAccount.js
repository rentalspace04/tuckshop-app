(function($){
    function clearSelectedTabs(elem) {
        $(elem).children("li").removeClass("selected");
    }

    function showForm(targetTab) {
        clearErrorMessage();
        var newTabID = $(targetTab).attr("data-form-id");
        var currentTabID = getCurrentTabID();
        // Don't do transition if they've clicked on the same tab
        if (newTabID == currentTabID) {
            return;
        }
        var newHeight = $("#" + newTabID).height();
        // Change the height to fit the new form
        $("#createAccountFormContainer").animate({"height": newHeight}, 400, function(){
            $("#createAccountFormContainer").css("height", "auto");
        });

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
            $("#" + getCurrentTabID() + " .errorMessage").text(message).slideDown(100);
        }
    }

    function clearErrorMessage(){
        $(".errorMessage").slideUp(100);
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
        getInput(currentTabID, "yearlevel").each(function(i, yl) {
            var level = $(yl).val();
            if (!isInteger(level) || (level <= 0 || level > 12)) {
                valid = false;
                showErrorMessage("Invalid Year Level of '" + level + "'");
                $(yl).addClass("error");
            }
        });

        if (!valid) {
            return false;
        }

        // Check that the date is valid
        // TODO

        // Check that the email is valid looking
        getInput(currentTabID, "email").each(function(i, emailInput) {
            var email = $(emailInput).val();
            // Should be <>@<>.<> (last <> can be <>.<>.<>....)
            var emailPattern = /.+@.+\..+/;
            if (!emailPattern.test(email)) {
                valid = false;
                showErrorMessage("Please give a valid email.");
                $(emailInput).addClass("error");
                return;
            }
        });

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

    // Checks that the email given isn't currently being used
    function checkEmail() {
        var email = getInput(getCurrentTabID(), "email").val();
        return new Promise(function(resolve, reject){
            new Ajax.Request('/data/checkEmail.php', {
                method: 'get',
                parameters: {
                    email: email
                },
                onSuccess: function(resp) {
                    // Check to see if there's JSON
                    var json = resp.responseJSON;
                    if (json) {
                        if (json.okay) {
                            resolve();
                        } else {
                            reject(Error("Email address is already in use."));
                        }
                    } else {
                        reject(Error("Request from server was invalid. Try again later."))
                    }
                },
                onFailure: function(resp) {
                    reject(Error("Unable to check the email... Try again later."));
                }
            });
        });
    }

    function getFormParameters() {
        var parameters = {};
        $("#" + getCurrentTabID() + " input").each(function(i, elem) {
            var name = $(elem).attr("name");
            var value = $(elem).val();
            parameters[name] = value;
        });
        return parameters;
    }

    function submitNewUser() {
        var params = getFormParameters();
        return new Promise(function(resolve, reject){
            new Ajax.Request("/data/newUser.php",{
                method: "post",
                parameters: params,
                onSuccess: function(resp) {
                    var json = resp.responseJSON;
                    if (json) {
                        if (json.okay) {
                            resolve();
                        } else {
                            reject(Error("Unable to submit user"));
                        }
                    } else {
                        reject(Error("The server didn't respond correctly. Try again later."));
                    }
                },
                onFailure: function (resp){
                    reject(Error("Unable to submit new user. Try again later"));
                }
            });
        });
    }

    // Form submit order is:
    //    - validate form
    //    - check email is unused (asynchronously)
    //    - try to submit data (async)
    function trySubmitForm(e) {
        e.preventDefault();

        //
        // REMEMBER TO REMOVE || true
        //
        if (validateForm() || true) {
            console.log("Form valid. Beginning submission");
            // Check the email doesn't belong to another user
            checkEmail().then(function() {
                    // Now that we know the email's okay try to submit the new
                    // user
                    submitNewUser().then(function(){
                        // The user was successfully submitted
                        console.log("submitted user");
                    }).catch(function(err){
                        // There was a problem submitting the user
                        console.error(err.message);
                    });
                }).catch(function(err){
                        showErrorMessage(err.message);
                        getInput(getCurrentTabID(), "email").addClass("error");
                    }
            ).then();
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