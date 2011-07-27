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
    // Configurate loading ajax content.
    $("#loading").ajaxStart(function()
    {
        $(this).show();
    });
    $("#loading").ajaxStop(function()
    {
        $(this).hide();
    });

    // Init user list.
    oInstanceUserList = new UserList();
    oInstanceUserList
        .setList($(".users_figure"))
        .setHiddenContainers($("#form_compare input[type=hidden][name^=compare]"))
        .init();

    // Handle form sent to convert its in ajax request.
    Forms.handleSent("compare_ajax", ManipulateCompare);
    Forms.handleSent("ajax_request", ManipulateResponse, function(oForm)
    {
        var sInputUser = $(oForm).find("input[type=search]").val();
        if (oInstanceUserList.checkIfExists(sInputUser))
        {
            showErrorMessage("User found yet!");
            return false;
        }
    });
});

ManipulateCompare =
{
    success: function(oResponse, oForm)
    {
        for (var nIndex = 0, nTotal = oResponse.length; nIndex < nTotal; nIndex++)
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
    }
};