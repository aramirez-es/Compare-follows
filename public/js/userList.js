/**
 * Class to manage user list.
 *
 * @author Alberto Ramirez Fernandez
 */
UserList = function()
{
    this.nCurrentSelected = 0;
    this.aListUsers = [];
    this.aVisibleUsers = [];
    this.aActivatedUsers = [];
    this.aInputUsers = [];
    this.aHiddenValues = [];
}
/**
 * Set an array with list of posibles users.
 *
 * @param aList Object
 */
UserList.prototype.setList = function(aList)
{
    this.aListUsers = aList;
    return this;
}
/**
 * Set an array with list of hidden input of users ids.
 *
 * @param aList Object
 */
UserList.prototype.setHiddenContainers = function(aList)
{
    this.aHiddenValues = aList;
    return this;
}
/**
 * Initialize the current UserList.
 */
UserList.prototype.init = function()
{
    this.nCurrentSelected = 0;
    this.aVisibleUsers.push(0);
}
/**
 * Add data from server to visible current user.
 *
 * @param oObjectToActivate Object
 * @param oUserData Object
 */
UserList.prototype.activate = function(oObjectToActivate, oUserData)
{
    $(oObjectToActivate).find("figcaption").text(oUserData.name);
    $(oObjectToActivate).find("img").attr("src", oUserData.picture).attr("alt", oUserData.name);
    $(oObjectToActivate).find("em").text("Followers: " + oUserData.followers + " / Followings: " + oUserData.followings);

    this.aActivatedUsers.push(this.nCurrentSelected);
    this.aInputUsers.push(oUserData.username);
    $(this.aHiddenValues[this.nCurrentSelected]).val(oUserData.username);
    this._showNext();

    return (this.nCurrentSelected - 1);
}
/**
 * Activate de next user to show if is possible.
 */
UserList.prototype._showNext = function()
{
    if (this.aListUsers.length === this.aVisibleUsers.length )
    {
        return null;
    }

    this.nCurrentSelected++;
    $(this.aListUsers[this.nCurrentSelected]).fadeIn("fast");
    this.aVisibleUsers.push(this.nCurrentSelected);
}
/**
 * Check if the given username has been found yet.
 *
 * @param sUsername string
 */
UserList.prototype.checkIfExists = function(sUsername)
{
    var nIndex = 0;
    var nFound = this.aInputUsers.length;

    do
    {
        if (this.aInputUsers[nIndex] === sUsername)
        {
            return true;
        }
        nIndex++;
    }
    while(nIndex < nFound);

    return false;
}