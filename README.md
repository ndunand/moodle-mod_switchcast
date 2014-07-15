
# Prerequisites:

 - Moodle version 2.7
 - for the full feature set, the SWITCHaai UniqueID of your users must be available as a Moodle user profile field
 - PHP with CURL module supporting HTTPS protobol
 - PHP with SimpleXML module


# Preliminary step:

Configure a SWITCHcast external authority. This will allow SWITCHcast to delegate authority to your Moodle regarding access rights to clips belonging to certain channels. More information available at http://help.switch.ch/cast/integration/ext_auth/ .

You will have to contact Switch in order to set this up; they will provide you with the necessary private keys and certificates.


# Module installation:

The latest version of the mod_switchcast Moodle activity plugin is available on GitHub : https://github.com/ndunand/moodle-mod_switchcast .

Install as any other activity module into your Moodle's mod/ directory, then visit http://your.moodle/admin/index.php to proceed to the module installation.


# Module setup:

Last, proceed to the module settings (via Site administration -> Plugins -> Activity modules -> SWITCHcast, or directly at http://your.moodle/admin/settings.php?section=modsettingswitchcast ) and fill in the following parameters:

 - SWITCHcast API URL: use the default value if not instructed otherwise by Switch

 - External authority host: use the default value

 - External authority ID: use the value provided by Switch

 - Enabled templates: use the default values, or contact Switch about using other templates

 - Allow production channels: enable this to allow production channels, be warned that these will be billed to your university!

 - Allow test channels: you may enable this to allow test channels, which have a very limited lifetime but are free for testing

 - XML cache lifetime: use the default value for best results

 - Display only used columns: enable this if you want only to display the used fields (columns) in the clip list, such as Recording Station, Owner, Actions. (This has a performance impact, because the list of all clips must be retrieved on each display.)

 - Logging enabled: enable only if necessary for troubleshooting

 - Default system account: this should be the System Account for your institution, as provided by Switch

 - Enabled institutions: this should be your institution's unique name, in the form "uniXX.ch"; you may here specify several institutions (coma-separated) if you have set up multi institution support with Switch (not necessary)

 - System account for ... (one for each enabled institution on the previous setting – visible only after having saved the settings): System Account for each enabled institution, as provided by Switch

 - AAI unique ID field: the Moodle profile field where the current user's SWITCHaai UniqueID can be found
   - if it's a regular profile field (such as "username"), simply enter its name here;
   - if it's an extended user profile field ( see http://your.moodle/user/profile/index.php ) use the synstax "user_info_data::X", where X is the extended user profile field ID

 - CA CRT file: full path to the file containing the Certification Authority certificate

 - Certificate file: full path to the X509 certificate file provided by Switch

 - SwitchCast key file: full path to the X509 private key file provided by Switch

 - SwitchCast key file password: a password to unlock the above key file if needed

 - Server key file: full path to the Web server's SSL private key file

 - Server key file password: a password to unlock the above key file if needed

 - curl proxy: proxy server to be used by CURL (leave empty if none)

