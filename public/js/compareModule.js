/**
 * Simple object to manipulate response of ajax request.
 */
ManipulateCompare =
{
    sName       : "",
    sPicture    : "",
    sUser       : "",
    success     : function(oResponse, oForm)
    {
        var nTotal      = oResponse.length;
        var oImage      = null;
        var oLink       = null;
        var oCompare    = null;
        var oContainer  = null;

        $("#compare_results").empty();
        $("#compare_results").append("<p>" + nTotal + " Amigos en común</p>");

        for (var nIndex = 0; nIndex < nTotal; nIndex++)
        {
            this.sName      = oResponse[nIndex].name;
            this.sPicture   = oResponse[nIndex].picture;
            this.sUser      = oResponse[nIndex].username;

            oImage      = this.createImage();
            oLink       = this.createLink(oImage);
            oCompare    = this.createCompareButton();
            oContainer  = this.createContainer(oLink, oCompare);

            $("#compare_results").append(oContainer);
        }

        this.attachEvent();
    },
    createImage : function()
    {
        oImage = new Image();

        $(oImage).attr("src", this.sPicture);
        $(oImage).attr("alt", this.sName);
        $(oImage).attr("width", 48);

        return oImage;
    },
    createLink  : function(oImage)
    {
        oLink = document.createElement("a");

        $(oLink).attr("href", "http://twitter.com/" + this.sUser);
        $(oLink).attr("title", this.sName);
        $(oLink).attr("target", "_blank");
        $(oLink).append(oImage);
        $(oLink).append(this.sName);

        return oLink;
    },
    createCompareButton: function()
    {
        oCompare = document.createElement("a");

        $(oCompare).attr("href", "#");
        $(oCompare).addClass("comparethis");
        $(oCompare).append("Compare it");

        return oCompare;
    },
    createContainer: function(oLink, oCompare)
    {
        oContainer = document.createElement("div");

        $(oContainer).attr("value", this.sUser);
        $(oContainer).addClass("onefriend");
        $(oContainer).append(oLink);
        $(oContainer).append(oCompare);

        return oContainer;
    },
    attachEvent: function()
    {
        $("a.comparethis").click(function(eEvent)
        {
            var oTarget = eEvent.target;
            alert("Compare " + $(oTarget).parent().attr("value"));
//            ManipulateResponse.success({}, {});

            return false;
        });
    }
};