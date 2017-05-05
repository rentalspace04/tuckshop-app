(function ($) {
    $(document).ready(function(){
        function showFormError(message) {
            $("#errorMessage").show().html("<p>" + message + "</p>");
        }

        function acceptLoginResult(resp) {
            console.log(resp);
            if (resp.responseJSON) {
                if (resp.responseJSON.auth) {
                    // If login was successful, redirect to home page
                    window.location.href = $("#login-form").attr("action");
                } else {
                    showFormError("Invalid username or password");
                }
            } else {
                // Show what the server responded with :/
                console.log(resp.responseText);
                showFormError("Something went wrong. Please try again.");
            }
        }

        function trySubmitForm() {
            // Only submit if form data is valid
            if (validateForm()) {
                console.log("Submitting login info");

                new Ajax.Request('/data/acceptLogin.php', {
                    method: 'post',
                    parameters: {
                        email: $("#email").val(),
                        password: $("#password").val()
                    },
                    onSuccess: acceptLoginResult
                });
            } else {
                // Check if the user fixes the form inputs
                $(".textbox.error").blur(checkIfStillError);
            }
        }

        function validateForm() {
            var result = true; // Form is good
            var email = $("#email").val();
            var password = $("#password").val();
            var message = "You need to give a";
            if (!email) {
                $("#email").addClass("error");
                result = false;
                message += "n email"
            }
            if (!password) {
                if (result) {
                    // the email was fine
                    message += " password";
                } else {
                    // email wasn't given either
                    message += " and password";
                }
                $("#password").addClass("error");
                result = false;
            }
            if (!result) {
                showFormError(message + "!");
            }
            return result;
        }

        // If the user puts data into a field that had no data, remove error
        function checkIfStillError(e) {
            $.each($(".textbox.error"), function(i, val){
                var textbox = $(val);
                if (textbox.val()) {
                    textbox.removeClass("error");
                }
            });
        }

        $("#login-form").submit(function(e) {
            e.preventDefault(); // Stop form from actually submitting

            console.log("Validating form before submission.");

            $("#errorMessage").hide();

            // Wait for 500ms so the user sees the feedback - i.e. form is invalid etc.
            setTimeout(trySubmitForm, 500)
        });
    });
})(jQuery);