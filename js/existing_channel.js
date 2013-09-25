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

(function($){

    $(document).ready(function() {

        var setChannelLink = M.cfg.wwwroot+'/mod/switchcast/getChannelInfo.php';


        // changing selected existing channel
        $("#id_ext_id").change(function() {
            $('input[type=submit]').prop('disabled', true);
            setChannel();
        })

        $("#id_channelnew").change(function() {
            if ($("#id_channelnew option:selected").val() == 'new channel') {
                // selected "new channel"
                unsetChannel();
            }
            else {
                // selected "existing channel"
                $('input[type=submit]').prop('disabled', true);
                setChannel();
            }
        })


        var setChannel = function() {
            $.ajax({
                url: setChannelLink,
                data: {
                    ext_id: $("#id_ext_id option:selected").val()
                },
                success: function(data) {
                    fillChannel(data);
                }
            });
        }


        var unsetChannel = function() {
            $('#id_newchannelname').val('');
            $('#id_disciplin').val('');
            $('#id_license').val('');
            $('#id_license').val('');
            $('#id_lifetime').val('');
            $('#id_lifetime').val('');
            $('#id_contenthours').val('');
            $('#id_department').val('');
            $('#id_annotations').val('');
            $('#id_template_id').val('');
            $('input[type=submit]').prop('disabled', false);
        }


        var fillChannel = function(data) {
            try {
                var json = JSON.parse(data);
            }
            catch(e) {
                // Logged out of LMS
                document.location.href = "/";
            }
            $("#id_channeltype").val(json.kind["0"]);
            $("#id_newchannelname").val(json.title["0"]);
            $('#id_disciplin').setSelect(json.discipline["0"]);
            $('#id_license').setSelect(json.license["0"]);
            $("#id_lifetime").val(json.lifetime["0"]);
            $("#id_contenthours").val(json.estimated_duration["0"]);
            $("#id_department").val(json.department["0"]);
            $("#id_annotations").val(json.allow_annotations["0"]);
            $("#id_template_id").val(json.template_id);
            $('input[type=submit]').prop('disabled', false);
        }

        $.fn.extend({
            setSelect : function(value) {
                var $options = $(this).find('option');
                $options.prop('selected', false);
                $options.each(function(){
                    if ($(this).text() == value) {
                        $(this).prop('selected', true);
                    }
                });
                return $(this);
            }
        });


        if ($("#id_channelnew option:selected").val() != 'new channel') {
            $('input[type=submit]').prop('disabled', true);
            setChannel();
        }

    });

})(jQuery)
