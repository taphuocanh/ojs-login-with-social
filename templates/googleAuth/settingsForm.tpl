{**
 * plugins/generic/socialsAuth/templates/googleAuth/settingsForm.tpl

 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Google Authentication settings
 *
 *}
<script>
    $(function() {ldelim}
        // Attach the form handler.
        $('#googleCredentialsSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
        {rdelim});
</script>
<form class="pkp_form" id="googleCredentialsSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="credentials-config"
type="google" save=true}">
    {csrf}
    {include file="controllers/notification/inPlaceNotification.tpl" notificationId="googleCredentialsSettingsFormNotification"}

    <div id="description">{translate key="plugins.generic.socialsAuth.manager.settings.googleAuth.description"}</div>

    {fbvFormArea id="webFeedSettingsFormArea"}
    {fbvElement type="text" id="google-app-client-id" name="googleAppClientID" value=$googleAppClientID label="plugins.generic.socialsAuth.manager.settings.googleAuth.googleAppClientID"}
    {fbvElement type="text" id="google-app-client-secret" name="googleAppClientSecret" value=$googleAppClientSecret label="plugins.generic.socialsAuth.manager.settings.googleAuth.googleAppClientSecret"}
    {fbvElement type="text" id="redirect-uri" name="redirectUri" value=$redirectUri label="plugins.generic.socialsAuth.manager.settings.googleAuth.redirectUri" disabled="disabled"}
    {/fbvFormArea}

    {fbvFormButtons}


    <p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
