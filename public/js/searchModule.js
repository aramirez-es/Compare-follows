/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateResponse =
{
    success: function(oReponse, oForm)
    {
        var oUser = oReponse;
        var oFigure = $(oForm).parent();
        var nCurrentActivaed = oInstanceUserList.activate(oFigure, oUser);
        $("#compare_results").empty();

        if (1 <= nCurrentActivaed)
        {
            $("#form_compare").fadeIn("fast");
            $("#form_compare").submit();
        }
    },
    error: function()
    {
        showErrorMessage("User not found.");
    },
    timeout: function()
    {
        showErrorMessage("Network do not respond.");
    },
    validate: function(oForm)
    {
        var bIsValid    = true;
        var sInputUser  = $(oForm).find("input[type=search]").val();

        if (false !== oInstanceUserList.checkIfExists(sInputUser))
        {
            showErrorMessage("User found yet!");
            bIsValid = false;
        }

        return bIsValid;
    }
};