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

    function validateForm() {
        var currentTabID = getCurrentTabID();
        var valid = true;

        $("#" + currentTabID + " input.required").each(function (i, elem) {
            if ($(elem).val() == "") {
                $(elem).addClass("error");
                valid = false;
            } else {
                $(elem).removeClass("error");
            }
        })

        return valid;
    }

    function transferFormData(name, from, to) {
        $("#" + to).find("input[name=\"" + name + "\"]").val(
            $("#" + from).find("input[name=\"" + name + "\"]").val()
        );
    }

    function trySubmitForm(e) {
        e.preventDefault();
        console.log(e);
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

        $("#createAccountParent").submit(trySubmitForm);
        $("#createAccountStudent").submit(trySubmitForm);
    });
})(jQuery);