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


(function ($) {

    $(document).ready(function () {

        var id = $('#switchcast-cmid-hidden-input').val(),
            the_table = $('#switchcast-clips-table');
        var directive = {
            'tr.switchcast-clip-template-row': {
                'entry<-entries': {
                    'img@src':                        'entry.cover',
                    'span.title':                     'entry.title',
                    'div.subtitle':                   'entry.subtitle',
                    'a.annotate@href':                'entry.AnnotationLink',
                    'a.flash@href':                   'entry.linkflash',
                    'a.mov@href':                     'entry.linkmov',
                    //'a.m4v@href':                     'entry.linkm4v',
                    'td.switchcast-presenter':        'entry.presenter',
                    'td.switchcast-location':         'entry.location',
                    'td.switchcast-recordingstation': 'entry.recordingstation',
                    'td.switchcast-recordingdate':    'entry.recordingdate',
                    'td.switchcast-owner':            'entry.owner_name',
                    'a.switchcast-editdetails@href':  'entry.editdetails_page',
//                    'a.switchcast-editclip@href' : 'entry.editclip_page',
// (we don't display this link anymore)
                    'a.switchcast-deleteclip@href':   'entry.deleteclip_page',
                    'a.switchcast-clipmembers@href':  'entry.clipmembers_page'
                }
            }
        };
        // cf. http://beebole.com/pure/documentation/get-started/
        var tfn = the_table.compile(directive);

        var scast_getclips = function () {

            $('.loading').show();
            $('.switchcast-clip-template-row').hide();

            var length = parseInt($('#menuswitchcast-perpage').val()),
                offset = parseInt($('#menuswitchcast-pageno').val() - 1) * length,
                filterstr = encodeURIComponent(
                    'title=' + $('input[name=switchcast-title]').val()
                    + '&presenter=' + $('input[name=switchcast-presenter]').val()
                    + '&location=' + $('input[name=switchcast-location]').val()
                    + '&recordingstation=' + $('input[name=switchcast-recordingstation]').val()
                    + '&ivt_owner=' + $('select[name=switchcast-owner]').val()
                    + '&withoutowner=' + $('input[name=switchcast-withoutowner]').is(':checked')
                ),
                sortkey = '',
                sortdir = '',
                json_url = M.cfg.wwwroot + '/mod/switchcast/get_events.php'
                    + '?id=' + id
                    + '&length=' + length
                    + '&offset=' + offset
                    + '&filterstr=' + filterstr;

            if ($('.switchcast-sortable a.sort').length > 0) {
                sortkey = $('.switchcast-sortable a.sort').parent().attr('data-sortkey'),
                    sortdir = $('.switchcast-sortable a.sort').attr('class').replace(/^sort /, '');
                json_url = json_url
                + '&sortkey=' + sortkey
                + '&sortdir=' + sortdir;
            }


            $.getJSON(json_url, function (data) {
                if (typeof data.error !== 'undefined') {
                    if (confirm(data.error)) {
                        document.location.reload();
                    }
                    return;
                }
                var items = {
                    entries: data.clips
                };
                var show_recordingstation = false,
                    show_owner = false,
                    show_actions = false,
                    show_location = false,
                    show_presenter = false,
                    theclip;
                if (typeof data.allclips != 'undefined') {
                    // let's figure which columns to display
                    for (var i = 0; i < data.allclips.length; i++) {
                        theclip = data.allclips[i];
                        if (theclip.owner_name && theclip.owner_name.length) {
                            show_owner = true;
                        }
                        if (theclip.recordingstation && theclip.recordingstation.length) {
                            show_recordingstation = true;
                        }
                        if (theclip.location && theclip.location.length) {
                            show_location = true;
                        }
                        if (theclip.presenter && theclip.presenter.length) {
                            show_presenter = true;
                        }
                        if (theclip.clipmembers_page != '#switchcast-inactive' || theclip.deleteclip_page != '#switchcast-inactive' || theclip.editdetails_page != '#switchcast-inactive') {
                            show_actions = true;
                        }
                    }
                    // but only show owner column if activity is set with is_ivt
                    show_owner = show_owner && $('table.switchcast-clips tr.switchcast-clip-template-row').hasClass('with-owner');
                }
                else {
                    // display all columns
                    show_owner = true;
                    show_recordingstation = true;
                    show_actions = true;
                    show_location = true;
                    show_presenter = true;
                }
                var nbpages = 1;
                the_table.html(tfn(items)); // cf. https://groups.google.com/forum/?fromgroups=#!topic/Pure-Unobtrusive-Rendering-Engine/78jEgjCd57c
                if (sortkey !== '' && sortdir !== '') {
                    $('.switchcast-clips-table th[data-sortkey=' + sortkey + ']').find('a').attr('class', 'sort ' + sortdir);
                }
                $('a[href=#switchcast-inactive], a:not([href])').remove();

                // only display table columns that are actually used
                if (!show_recordingstation) {
                    $('.switchcast-recordingstation').hide();
                }
                else {
                    $('.switchcast-recordingstation').show();
                }

                if (!show_location) {
                    $('.switchcast-location').hide();
                }
                else {
                    $('.switchcast-location').show();
                }

                if (!show_presenter) {
                    $('.switchcast-presenter').hide();
                }
                else {
                    $('.switchcast-presenter').show();
                }

                if (!show_owner) {
                    $('.switchcast-owner').hide();
                }
                else {
                    $('.switchcast-owner').show();
                }

                if (!show_actions) {
                    $('.switchcast-actions').hide();
                }
                else {
                    $('.switchcast-actions').show();
                }

                // hide action hint icon if no actions
                $('td.switchcast-actions').each(function () {
                    var $this = $(this);
                    if ($this.find('a').length == 0) {
                        $this.addClass('switchcast-actions-empty');
                    }
                });

                var from = (offset + 1).toString();
                if (data.count == 0) {
                    from = 0;
                }
                $('.switchcast-cliprange-from').text(from);
                $('.switchcast-cliprange-to').text(Math.min(offset + length, data.count).toString());
                $('.switchcast-cliprange-of').text(data.count.toString());
                $('.loading').hide();
                $('.ajax-controls-pagination').show();
                $('.switchcast-clip-template-row').show();
                nbpages = Math.ceil(data.count / length);
                $('#menuswitchcast-pageno option').remove();
                for (var i = 1; i <= nbpages; i++) {
                    $('#menuswitchcast-pageno').append($('<option value="' + i + '">' + i + '</option>'));
                }
                $('#menuswitchcast-pageno').val(offset / length + 1);

                // put checkbox back in previous position
                if (modswitchcast_tbldsplaypar.showsubtitles === true) {
                    $('#clip-show-subtitle').click();
                }

            });

        };

        scast_getclips();

        $('.menuswitchcast-pageno').change(function () {
            scast_getclips();
        });

        $('.menuswitchcast-perpage').change(function () {
            if ($(this).val() !== '') {
                $('.menuswitchcast-pageno').val('1');
                scast_getclips();
            }
        });

        $('.switchcast-filters button.cancel').click(function () {
            $('.switchcast-filters').find('input, select').val('');
            $('.switchcast-filters').find('input, select').prop('checked', false);
            scast_getclips();
        });

        $('.switchcast-filters button.ok').click(function () {
            scast_getclips();
        });

    })

})(jQuery)

