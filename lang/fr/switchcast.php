<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    mod
 * @subpackage switchcast
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['answered'] = 'Répondu';
$string['channel'] = 'Canal';
$string['channelnew'] = 'Nouveau canal';
$string['channelchoose'] = 'Canal sélectionné';
$string['channelexisting'] = 'Canal existant';
$string['channeltype'] = 'Type de canal';
$string['channelprod'] = 'Canal de production';
$string['channeltest'] = 'Canal de test';
$string['completionsubmit'] = 'Show as complete when user makes a choice';
$string['displayhorizontal'] = 'Affichage horizontal';
$string['displaymode'] = 'Mode d\'affichage';
$string['displayvertical'] = 'Affichage vertical';
$string['expired'] = 'Désolé, cette activité est fermée depuis {$a}; elle n\'est plus disponible.';
$string['fillinatleastoneoption'] = 'Vous devez fournir au moins deux réponses possibles.';
$string['full'] = 'Complet';
$string['switchcastclose'] = 'Jusqu\'à';
$string['switchcastname'] = 'Nom SWITCHcast';
$string['switchcastopen'] = 'Ouvert depuis';
$string['switchcastoptions'] = 'Options SWITCHcast';
$string['chooseaction'] = 'Choisissez une action ...';
$string['limit'] = 'Limite';
$string['limitanswers'] = 'Limiter le nombre de réponses permises';
$string['modulename'] = 'Canal SWITCHcast';
$string['modulename_help'] = 'Le module SWITCHcast permet aux enseignants de gérer un canal directement à partir de cet espace Moodle.';
$string['modulenameplural'] = 'Canaux SWITCHcast';
$string['mustchooseone'] = 'Vous devez choisir une réponse avant d\'enregistrer. Rien a été enregistré.';
$string['noresultsviewable'] = 'Les résultats ne sont pas disponibles actuellement.';
$string['notanswered'] = 'Pas encore répondu';
$string['notopenyet'] = 'Désolé, cette activité n\'est pas disponible avant {$a}.';
$string['pluginadministration'] = 'Administration SWITCHcast';
$string['pluginname'] = 'SWITCHcast';
$string['timerestrict'] = 'Limiter les réponses à cette période';
$string['viewallresponses'] = 'Visualiser {$a} réponses';
$string['withselected'] = 'Avec la sélection';
$string['yourselection'] = 'Votre sélection';
$string['skipresultgraph'] = 'Ne pas afficher le graphique des résultats';
$string['moveselectedusersto'] = 'Déplacer l\'utilisateur sélectionné vers...';
$string['numberofuser'] = 'Nombre d\'utilisateurs';
$string['uid_field'] = 'SWITCHaai unique ID';
$string['uid_field_desc'] = 'Profil d\'utilisateur qui contient le SWITCHaai unique ID, sous la forme &lt;fieldname&gt; OR &lt;table::fieldid&gt;.';
$string['switch_api_host'] = 'SWITCHcast API URL';
$string['switch_api_host_desc'] = 'Adresse du service Web SWITCHcast';
$string['default_sysaccount'] = 'Compte système par défault';
$string['default_sysaccount_desc'] = 'Compte système utilisé pour les appels API SWITCHcast';
$string['sysaccount'] = 'Compte système pour {$a}';
$string['sysaccount_desc'] = 'Compte à utiliser pour les appels API SWITCHcast API de {$a}.';
$string['cacrt_file'] = 'Fichier CA CRT';
$string['cacrt_file_desc'] = 'Certification authority Certificate file';
$string['crt_file'] = 'Certificate file';
$string['crt_file_desc'] = 'x509 Server Certificate file';
$string['castkey_file'] = 'Switchcast key file';
$string['castkey_file_desc'] = 'La clé fournie par SWITCHcast pour signer les appels API';
$string['castkey_password'] = 'Switchcast key file password';
$string['castkey_password_desc'] = 'Le mot de passe nécessaire pour déverrouiller la clé SWITCHCast';
$string['serverkey_file'] = 'Server key File';
$string['serverkey_file_desc'] = 'SSL key File de ce serveur';
$string['serverkey_password'] = 'Server key file password';
$string['serverkey_password_desc'] = 'Le mot de passe nécessaire pour déverrouiller la clé Serveur';
$string['enabled_institutions'] = 'Institutions activées';
$string['enabled_institutions_desc'] = 'Une liste des institutions activées sur ce serveur Moodle (valeurs séparées par des virgules).';
$string['external_authority_host'] = 'External authority host';
$string['external_authority_host_desc'] = 'External authority host URL';
$string['external_authority_id'] = 'External authority ID';
$string['external_authority_id_desc'] = 'External authority ID chez SWITCHcast';
$string['metadata_export'] = 'Export des métadonnées';
$string['metadata_export_desc'] = '';
//$string['configuration_id'] = 'Configuration ID';
//$string['configuration_id_desc'] = '';
//$string['streaming_configuration_id'] = 'Streaming configuration ID';
//$string['streaming_configuration_id_desc'] = '';
$string['access'] = 'Accès';
$string['access_desc'] = 'Niveau d\'accès pour les canaux créés';
$string['allow_test_channels'] = 'Permettre des canaux de test';
$string['allow_test_channels_desc'] = 'Cochez si vous voulez permettre la création de canaux de test.';
$string['allow_prod_channels'] = 'Permettre des canaux de production';
$string['allow_prod_channels_desc'] = 'Cochez si vous voulez permettre la création de canaux de production';
$string['misconfiguration'] = 'Le plugin n\'est pas configuré correctement, contactez l\'administrateur du site.';
$string['channeltypeforbidden'] = 'La création de {$a} canaux n\'est pas permise, contactez l\'administrateur du site.';
$string['logging_enabled'] = 'Journal activé';
$string['logging_enabled_desc'] = 'Enregistrer tous les appels et réponses XML à l\'API.<br />Le fichier journal se trouve à {$a}/mod/switchcast/switchcast_api.log';
$string['display_select_columns'] = 'Afficher seulement les colonnes effectivement utilisées';
$string['display_select_columns_desc'] = 'Dans la liste des clips, afficher seulement les champs (colonnes) utilisés, comme par exemple Station d\'enregistrement, Propriétaire, Actions. Ce choix a un impact sur la performance, parce que la liste de tous les clips doit être téléchargée pour chaque affichage.';
$string['enabled_templates'] = 'Templates activés';
$string['enabled_templates_desc'] = 'Listez ici tous les templates SWITCHcast que vous voulez activer pour votre institution; le choix du template est seulement possible au moment de la création d\'un nouveau canal. Une définition par ligne, avec le format suivant : <em>&lt;TEMPLATE_ID&gt;::&lt;TEMPLATE_NAME&gt;</em>.<br />Si vous voulez utiliser pour un template le nom officiel de SWITCH, vous pouvez omettre l\'élément TEMPLATE_NAME (mais pas les séparateurs).';
$string['newchannelname'] = 'Nom du nouveau canal';
$string['license'] = 'Licence';
$string['disciplin'] = 'Discipline';
$string['contenthours'] = 'Volume estimé du contenu vidéo (en heures)';
$string['lifetime'] = 'Durée de vie estimée du contenu vidéo';
$string['months'] = '{$a} mois';
$string['years'] = '{$a} années';
$string['department'] = 'Département';
$string['annotations'] = 'Annotations';
$string['annotationsyes'] = 'Avec annotations';
$string['annotationsno'] = 'Sans annotations';
$string['template_id'] = 'Template Switchcast';
$string['is_ivt'] = 'Accès individuel par clip';
$string['inviting'] = 'Propriétaire du clip peut inviter';
$string['clip_member'] = 'Participants invités au clip';
$string['channel_teacher'] = 'Enseignant';
$string['untitled_clip'] = '(clip sans titre)';
$string['no_owner'] = '(pas de propriétaire)';
$string['owner_not_in_moodle'] = '(Propriétaire du clip pas enregistré dans Moodle)';
$string['clip_no_access'] = 'Vous n\'avez pas accès au clip';
$string['upload_clip'] = 'Déposer un nouveau clip';
$string['edit_at_switch'] = 'Editer ce canal sur le serveur SWITCHcast';
$string['edit_at_switch_short'] = 'Editer sur SWITCHcast';
$string['switchcast:use'] = 'Afficher le contenu du canal SWITCHcast';
$string['switchcast:isproducer'] = 'Enregistré comme producteur du canal SWITCHcast (et donc avec accès à tous les clips)';
$string['switchcast:addinstance'] = 'Ajouter une nouvelle activité SWITCHcast';
$string['switchcast:seeallclips'] = 'Peut voir tous les clips dans un cours';
$string['nologfilewrite'] = 'Impossible d\'écrire le fichier journal : {$a}. Vérifiez les permissions du système de fichiers.';
$string['noclipsinchannel'] = 'Ce canal ne contient pas de clips.';
$string['novisibleclipsinchannel'] = 'Ce canal ne contient pas de clips auxquels vous ayez accès.';
$string['user_notaai'] = 'La création d\'un nouveau canal nécessite un compte SWITCHaai.';
$string['user_homeorgnotenabled'] = 'La création d\'une activité SWITCHcast nécessite l\'activation de votre HomeOrganization ({$a}) au niveau du site ; veuillez contacter l\'administrateur.';
$string['clip'] = 'Clip';
$string['cliptitle'] = 'Clip – Titre';
$string['presenter'] = 'Présentateur-trice';
$string['location'] = 'Lieu';
$string['recording_station'] = 'Station d\'enregistrement';
$string['date'] = 'Date';
$string['owner'] = 'Propriétaire';
$string['actions'] = 'Actions';
$string['editmembers'] = 'Gérer les invitations au clip';
$string['addmember'] = 'Inviter un participant au clip';
$string['editmembers_long'] = 'Gérer les participants invités au clip';
$string['setowner'] = 'Définir le propriétaire';
$string['delete_clip'] = 'Supprimer le clip';
$string['flash'] = 'Flash';
$string['mov'] = 'QuickTime';
$string['m4v'] = 'Smartphone';
$string['context'] = 'Contexte';
$string['confirm_removeuser'] = 'Voulez-vous vraiment supproimer cet utilisateur ?';
$string['delete_clip_confirm'] = 'Voulez-vous vraiment supprimer ce clip ?';
$string['back_to_channel'] = 'Retourner à la vue d\'ensemble du canal';
$string['channel_several_refs'] = 'Ce canal est référencé dans d\'autres activités Moodle.';
$string['set_clip_owner'] = 'Définir le propriétaire du clip';
$string['owner_no_switch_account'] = 'Il est impossible de définir &laquo;{$a}&raquo; comme propiétaire de ce clip, parce que cet utilisateur-trice n\'a pas de compte SWITCHaai.';
$string['nomoreusers'] = 'Il n\'y a plus d\'utilisateurs disponibles à ajouter.';
$string['nocontenthours'] = 'La valeur minimale pour le volume estimé de contenu (en heures) est de 1.';
$string['nodepartment'] = 'Vous devez compléter le champ Département.';
$string['setnewowner'] = 'Définir comme nouveau propriétaire';
$string['clip_owner'] = 'Propriétaire du clip';
$string['group_member'] = 'Membre du groupe';
$string['aaiid_vs_moodleid'] = 'Le SWITCHaai Unique Id ne correspond pas à l\'identifiant Moodle !';
$string['error_decoding_token'] = 'Erreur de décodage du jeton: {$a}';
$string['error_opening_privatekey'] = 'Erreur de lecture du fichier de clef privée : {$a}';
$string['error_decrypting_token'] = 'Erreur de déchiffrement du jeton : {$a}';
$string['channelhasotherextauth'] = 'Ce canal est déjà lié à une autre External Authority: <em>{$a}</em>.';
$string['novisiblegroups'] = 'Ce paramètre n\'est pas disponible pour cette activité.';
$string['nogroups_withoutivt'] = 'L\'option groupes séparés est seulement activé si le paramètre &laquo;Activer l\'accès individuel par clip&raquo; ci-dessus est activé.';
$string['itemsperpage'] = 'Clips par page';
$string['pageno'] = 'Page n° ';
$string['pagination'] = 'Affichage des clips <span class="switchcast-cliprange-from"></span> à <span class="switchcast-cliprange-to"></span> sur <span class="switchcast-cliprange-of"></span>.';
$string['filters'] = 'Filtres';
$string['resetfilters'] = 'Remettre les filtres à zéro';
$string['title'] = 'Titre';
$string['recordingstation'] = 'Station d\'enregistrement';
$string['withoutowner'] = 'Pas de propriétaire';
$string['notavailable'] = 'Désolé, cette activité est encore en phase de test et n\'est pas disponible pour le moment.';
$string['xml_cache_time'] = 'Durée de vie du cache XML';
$string['xml_cache_time_desc'] = 'Pour combien de temps (en secondes) les réponses XML du serveur SWITCHCast doivent-elles être mises en cache ? Une valeur de 0 correspond à la désactivation du cache.';
$string['removeowner'] = 'Retirer le propriétaire';
$string['channeldoesntexist'] = 'Le canal lié n\'existe pas (plus ?)';
$string['channeldoesnotbelong'] = 'Le canal lié appartient à une autre organisation ({$a}) ; vous ne pouvez donc pas le modifier. Seul un enseignant de {$a} peut le modifier.';
$string['switch_api_down'] = 'Le serveur SwitchCast ne répond pas.';
$string['xml_fail'] = 'Erreur de communication avec le serveur SwitchCast.';
$string['badorganization'] = 'L\'organisation liée à ce canal n\'est pas configurée correctement.';
$string['curl_proxy'] = 'curl proxy';
$string['curl_proxy_desc'] = 'Si curl doit passer par un proxy, le spécifier ici sous la forme <em>proxyhostname:port</em>';
