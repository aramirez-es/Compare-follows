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
        var self = this;
        $(this).show();
        setTimeout(function()
        {
            $(self).hide();
            showErrorMessage("Network do not respond.");
        }, 10000);
    });
    $("#loading").ajaxStop(function()
    {
        $(this).hide();
    });

    // Init user list.
    oInstanceUserList = new UserList();
    oInstanceUserList.setList($(".users_figure")).init();

    // Handle form sent to convert its in ajax request.
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

/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateResponse =
{
    success: function(oReponse, oForm)
    {
        var oUser = oReponse;
        var oFigure = $(oForm).parent();
        oInstanceUserList.activate(oFigure, oUser);
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