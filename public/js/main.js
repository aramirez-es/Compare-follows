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