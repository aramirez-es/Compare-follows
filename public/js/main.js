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
 * Attach events to Dom
 */
function attachEventsToDom()
{
    // Attacht event on change friend type.
    $("input[type=radio][name^=compare]").change(function()
    {
        $("#form_compare").submit();
    });
}

/**
 * Instance of class to manage list of users.
 *
 * @var UserList
 */
var oInstanceUserList = null;

/**
 * Tracking for GA.
 *
 * @var Object
 */
var _gaq = _gaq || [];

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

    _gaq.push(['_setAccount', 'UA-4885186-3']);
    _gaq.push(['_trackPageview']);

    // Handle form sent to convert its in ajax request.
    Forms.handleSent("compare_ajax", ManipulateCompare);
    Forms.handleSent("ajax_request", ManipulateResponse);

    attachEventsToDom();
});