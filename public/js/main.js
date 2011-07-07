/**
 * Javascript file to manage actions.
 *
 * @author Alberto Ramírez.
 */

$(document).ready(function()
{
    // Handle form sent to convert its in ajax request.
    Forms.handleSent("ajax_request", ManipulateResponse);
});

/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateResponse =
{
    success: function(oReponse){ alert(oReponse); },
    error: function(){ alert("Error"); },
    timeout: function(){ alert("Timeout"); }
};