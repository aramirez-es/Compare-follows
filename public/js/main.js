/**
 * Javascript file to manage actions.
 *
 * @author Alberto Ramírez.
 */

/**
 * Function to show an error message.
 *
 * @param sMessage string
 */
showErrorMessage = function(sMessage)
{
    $("#message").text(sMessage)
        .slideDown("fast")
        .delay(2000)
        .fadeOut("slow");
}

/**
 * Configurate view on ajax start and stop
 */
configurateAjaxLoading = function()
{
    $("#loading").ajaxStart(function()
    {
        $(this).show();
    });
    $("#loading").ajaxStop(function()
    {
        $(this).hide();
    });
}

/**
 * Instance of class to manage list of users.
 *
 * @var UserList
 */
var oInstanceUserList = null;

/**
 * Document is loaded.
 */
$(document).ready(function()
{
    configurateAjaxLoading();

    oInstanceUserList = new UserList();
    oInstanceUserList
        .setList($(".users_figure"))
        .setHiddenContainers($("#form_compare input[type=hidden][name^=compare]"))
        .init();

    // Handle form sent to convert its in ajax request.
    Forms.handleSent("compare_ajax", ManipulateCompare);
    Forms.handleSent("ajax_request", ManipulateResponse);
});

/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateCompare =
{
    success: function(oResponse, oForm)
    {
        var nTotal = oResponse.length;
        $("#compare_results").empty();
        $("#compare_results").append("<p>" + nTotal + " Amigos en común</p>");

        for (var nIndex = 0; nIndex < nTotal; nIndex++)
        {
            $("#compare_results").append("<p>" + oResponse[nIndex].name + "</p>");
        }
    }
};

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

        if (1 === nCurrentActivaed)
        {
            $("#form_compare").fadeIn("fast");
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

        if (oInstanceUserList.checkIfExists(sInputUser))
        {
            showErrorMessage("User found yet!");
            bIsValid = false;
        }

        return bIsValid;
    }
};