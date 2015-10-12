<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/class.xoctWaiterGUI.php');

/**
 * Class xoctSeriesFormGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xoctSeriesFormGUI extends ilPropertyFormGUI {

    const F_COURSE_NAME = 'course_name';
    const F_TITLE = 'title';
    const F_DESCRIPTION = 'description';
    const F_CHANNEL_TYPE = 'channel_type';
    const EXISTING_NO = 1;
    const EXISTING_YES = 2;
    const F_INTRODUCTION_TEXT = 'introduction_text';
    const F_INTENDED_LIFETIME = 'intended_lifetime';
    const F_EST_VIDEO_LENGTH = 'est_video_length';
    const F_LICENSE = 'license';
    const F_DISCIPLINE = 'discipline';
    const F_DEPARTMENT = 'department';
    const F_STREAMING_ONLY = 'streaming_only';
    const F_USE_ANNOTATIONS = 'use_annotations';
    const F_PERMISSION_PER_CLIP = 'permission_per_clip';
    const F_ACCEPT_EULA = 'accept_eula';
    const F_EXISTING_IDENTIFIER = 'existing_identifier';
    /**
     * @var  xoctSeries
     */
    protected $object;
    /**
     * @var xoctSeriesGUI
     */
    protected $parent_gui;
    /**
     * @var  ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilOpenCastPlugin
     */
    protected $pl;
    /**
     * @var bool
     */
    protected $external = true;

    /**
     * @param              $parent_gui
     * @param xoctOpenCast $cast
     * @param bool         $view
     * @param bool         $infopage
     * @param bool         $external
     */
    public function __construct($parent_gui, xoctOpenCast $cast, $view = false, $infopage = false, $external = true) {
        global $ilCtrl, $lng, $tpl;
        $this->cast = $cast;
        $this->series = $cast->getSeries();
        $this->parent_gui = $parent_gui;
        $this->ctrl = $ilCtrl;
        $this->pl = ilOpenCastPlugin::getInstance();
        $this->ctrl->saveParameter($parent_gui, xoctSeriesGUI::SERIES_ID);
        $this->ctrl->saveParameter($parent_gui, 'new_type');
        $this->lng = $lng;
        $this->is_new = ($this->series->getIdentifier() == '');
        $this->view = $view;
        $this->infopage = $infopage;
        $this->external = $external;
        xoctWaiterGUI::init();
        $tpl->addJavaScript($this->pl->getStyleSheetLocation('default/existing_channel.js'));
        if ($view) {
            $this->initView();
        }
        else {
            $this->initForm();
        }
    }

    protected function initForm() {
        $this->setTarget('_top');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->initButtons();
        if ($this->is_new) {
            $existing_channel = new ilRadioGroupInputGUI($this->txt(self::F_CHANNEL_TYPE), self::F_CHANNEL_TYPE);
            {
                $existing = new ilRadioOption($this->txt('existing_channel_yes'), self::EXISTING_YES);
                {
                    $existing_identifier =
                            new ilSelectInputGUI($this->txt(self::F_EXISTING_IDENTIFIER), self::F_EXISTING_IDENTIFIER);
                    require_once('class.xoctSeries.php');
                    $existing_series = [];
                    foreach (xoctSeries::getAllForUser('fschmid@unibe.ch') as $serie) {
                        $existing_series[$serie->getIdentifier()] = $serie->getTitle();
                    }
                    //				sort($existing_series);
                    $existing_identifier->setOptions($existing_series);
                    $existing->addSubItem($existing_identifier);
                }
                $existing_channel->addOption($existing);

                $new = new ilRadioOption($this->txt('existing_channel_no'), self::EXISTING_NO);
                $existing_channel->addOption($new);
            }

            $this->addItem($existing_channel);
        }

        $te = new ilTextInputGUI($this->txt(self::F_TITLE), self::F_TITLE);
        $te->setRequired(true);
        $this->addItem($te);

        $te = new ilTextAreaInputGUI($this->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($te);

        $te = new ilTextAreaInputGUI($this->txt(self::F_INTRODUCTION_TEXT), self::F_INTRODUCTION_TEXT);
        $te->setRows(5);
        $this->addItem($te);

        //		$discipline = new ilSelectInputGUI($this->txt(self::F_DISCIPLINE), self::F_DISCIPLINE);
        //		sort(self::$disciplines);
        //		$discipline->setOptions(self::$disciplines);
        //		$discipline->setRequired(true);
        //		$this->addItem($discipline);

        $license = new ilSelectInputGUI($this->txt(self::F_LICENSE), self::F_LICENSE);
        $license->setOptions([
                'http://creativecommons.org/licenses/by/2.5/ch/'       => 'CC: Attribution',
                'http://creativecommons.org/licenses/by-nc/2.5/ch/'    => 'CC: Attribution-Noncommercial',
                'http://creativecommons.org/licenses/by-nc-nd/2.5/ch/' => 'CC: Attribution-Noncommercial-No Derivative Works',
                'http://creativecommons.org/licenses/by-nc-sa/2.5/ch/' => 'CC: Attribution-Noncommercial-Share Alike',
                'http://creativecommons.org/licenses/by-nd/2.5/ch/'    => 'CC: Attribution-No Derivative Works',
                'http://creativecommons.org/licenses/by-sa/2.5/ch/'    => 'CC: Attribution-Share Alike',
                null                                                   => 'As defined in content',
        ]);
        $this->addItem($license);

        //		$est_video_length = new ilNumberInputGUI($this->txt(self::F_EST_VIDEO_LENGTH), self::F_EST_VIDEO_LENGTH);
        //		$est_video_length->setMinValue(1);
        //		$est_video_length->setInfo($this->infoTxt(self::F_EST_VIDEO_LENGTH));
        //		$est_video_length->setRequired(true);
        //		$this->addItem($est_video_length);

        //		$intended_lifetime = new ilSelectInputGUI($this->txt(self::F_INTENDED_LIFETIME), self::F_INTENDED_LIFETIME);
        //		$intended_lifetime->setInfo($this->infoTxt(self::F_INTENDED_LIFETIME));
        //		$intended_lifetime->setOptions(array(
        //			6 => '6 month',
        //			12 => '1 year',
        //			24 => '2 years',
        //			36 => '3 years',
        //			60 => '4 years',
        //			72 => '5 years',
        //		));
        //		$intended_lifetime->setRequired(true);
        //		$this->addItem($intended_lifetime);

        $department = new ilTextInputGUI($this->txt(self::F_DEPARTMENT), self::F_DEPARTMENT);
        $department->setInfo($this->infoTxt(self::F_DEPARTMENT));
        $this->addItem($department);

        $use_annotations = new ilCheckboxInputGUI($this->txt(self::F_USE_ANNOTATIONS), self::F_USE_ANNOTATIONS);
        $this->addItem($use_annotations);

        $streaming_only = new ilCheckboxInputGUI($this->txt(self::F_STREAMING_ONLY), self::F_STREAMING_ONLY);
        $this->addItem($streaming_only);

        $permission_per_clip =
                new ilCheckboxInputGUI($this->txt(self::F_PERMISSION_PER_CLIP), self::F_PERMISSION_PER_CLIP);
        $permission_per_clip->setInfo($this->infoTxt(self::F_PERMISSION_PER_CLIP));
        $this->addItem($permission_per_clip);

        if ($this->is_new) {
            $accept_eula = new ilCheckboxInputGUI($this->txt(self::F_ACCEPT_EULA), self::F_ACCEPT_EULA);
            $accept_eula->setInfo('MISSING EULA TEXT');
            $this->addItem($accept_eula);
        }
    }

    public function fillFormRandomized() {
        $array = [
                self::F_CHANNEL_TYPE      => self::EXISTING_YES, self::F_CHANNEL_TYPE => self::EXISTING_NO,
                self::F_TITLE             => 'New Channel ' . date(DATE_ATOM),
                self::F_DESCRIPTION       => 'This is a description',
                self::F_INTRODUCTION_TEXT => 'We don\'t need no intro text',
                self::F_LICENSE           => $this->series->getLicense(), self::F_USE_ANNOTATIONS => true,
                self::F_STREAMING_ONLY    => true, self::F_PERMISSION_PER_CLIP => true,
        ];

        $this->setValuesByArray($array);
    }

    public function fillForm() {
        $array = [
                self::F_CHANNEL_TYPE        => self::EXISTING_NO, self::F_TITLE => $this->series->getTitle(),
                self::F_DESCRIPTION         => $this->series->getDescription(),
                self::F_INTRODUCTION_TEXT   => $this->cast->getIntroText(),
                self::F_LICENSE             => $this->series->getLicense(),
                self::F_USE_ANNOTATIONS     => $this->cast->getUseAnnotations(),
                self::F_STREAMING_ONLY      => $this->cast->getStreamingOnly(),
                self::F_PERMISSION_PER_CLIP => $this->cast->getPermissionPerClip(),
        ];

        $this->setValuesByArray($array);
    }

    /**
     * returns whether checkinput was successful or not.
     *
     * @return bool
     */
    public function fillObject() {
        if (!$this->checkInput()) {
            return false;
        }
        if ($this->getInput(self::F_CHANNEL_TYPE) == self::EXISTING_YES) {
            $this->series->setIdentifier($this->getInput(self::F_EXISTING_IDENTIFIER));
        }
        $this->series->setTitle($this->getInput(self::F_TITLE));
        $this->series->setDescription($this->getInput(self::F_DESCRIPTION));
        $this->cast->setIntroText($this->getInput(self::F_INTRODUCTION_TEXT));
        $this->series->setLicense($this->getInput(self::F_LICENSE));
        $this->cast->setUseAnnotations($this->getInput(self::F_USE_ANNOTATIONS));
        $this->cast->setStreamingOnly($this->getInput(self::F_STREAMING_ONLY));
        $this->cast->setPermissionPerClip($this->getInput(self::F_PERMISSION_PER_CLIP));

        return true;
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function txt($key) {
        return $this->pl->txt('series_' . $key);
    }

    /**
     * @param $key
     *
     * @return string
     */
    protected function infoTxt($key) {
        return $this->pl->txt('series_' . $key . '_info');
    }

    /**
     * @return bool|string
     */
    public function saveObject() {
        if (!$this->fillObject()) {
            return false;
        }
        if ($this->series->getIdentifier()) {
            $this->cast->setSeriesIdentifier($this->series->getIdentifier());
            $this->series->update();
            if ($this->is_new) {
                $this->cast->create();
            }
            else {
                $this->cast->update();
            }
        }
        else {
            $this->series->create();
            $this->cast->setSeriesIdentifier($this->series->getIdentifier());
        }

        return $this->cast;
    }

    protected function initButtons() {
        if ($this->is_new) {
            $this->setTitle($this->txt('create'));
            $this->addCommandButton(xoctSeriesGUI::CMD_CREATE, $this->txt(xoctSeriesGUI::CMD_CREATE));
        }
        else {
            $this->setTitle($this->txt('edit'));
            $this->addCommandButton(xoctSeriesGUI::CMD_UPDATE, $this->txt(xoctSeriesGUI::CMD_UPDATE));
        }

        $this->addCommandButton(xoctSeriesGUI::CMD_CANCEL, $this->txt(xoctSeriesGUI::CMD_CANCEL));
    }

    /**
     * Workaround for returning an object of class ilPropertyFormGUI instead of this subclass
     * this is used, until bug (http://ilias.de/mantis/view.php?id=13168) is fixed
     *
     * @return ilPropertyFormGUI This object but as an ilPropertyFormGUI instead of a xdglRequestFormGUI
     */
    public function getAsPropertyFormGui() {
        $ilPropertyFormGUI = new ilPropertyFormGUI();
        $ilPropertyFormGUI->setFormAction($this->getFormAction());
        $ilPropertyFormGUI->setTitle($this->getTitle());

        $ilPropertyFormGUI->addCommandButton(xoctSeriesGUI::CMD_SAVE, $this->lng->txt(xoctSeriesGUI::CMD_SAVE));
        $ilPropertyFormGUI->addCommandButton(xoctSeriesGUI::CMD_CANCEL, $this->lng->txt(xoctSeriesGUI::CMD_CANCEL));
        foreach ($this->getItems() as $item) {
            $ilPropertyFormGUI->addItem($item);
        }

        return $ilPropertyFormGUI;
    }
    //
    //
    //	public function addToInfoScreen(ilInfoScreenGUI $ilInfoScreenGUI) {
    //	}
    //
    //
    protected function initView() {
        $this->initForm();
        /**
         * @var $item ilNonEditableValueGUI
         */
        foreach ($this->getItems() as $item) {
            $te = new ilNonEditableValueGUI($this->txt($item->getPostVar()), $item->getPostVar());
            $this->removeItemByPostVar($item->getPostVar());
            $this->addItem($te);
        }
    }

    protected static $disciplines = [
            1932 => 'Arts & Culture', 5314 => 'Architecture', 6302 => 'Landscape architecture',
            5575 => 'Spatial planning', 9202 => 'Art history', 3119 => 'Design', 6095 => 'Industrial design',
            5103 => 'Visual communication', 5395 => 'Film', 8202 => 'Music', 2043 => 'Music education',
            9610 => 'School and church music', 3829 => 'Theatre', 1497 => 'Visual arts', 6950 => 'Business',
            1676 => 'Business Administration', 4949 => 'Business Informatics', 7290 => 'Economics',
            2108 => 'Facility Management', 7641 => 'Hotel business', 6238 => 'Tourism', 5214 => 'Education',
            1672 => 'Logopedics', 1406 => 'Pedagogy', 3822 => 'Orthopedagogy', 2150 => 'Special education',
            9955 => 'Teacher education', 6409 => 'Primary school', 7008 => 'Secondary school I',
            4233 => 'Secondary school II', 8220 => 'Health', 2075 => 'Dentistry', 5955 => 'Human medicine',
            5516 => 'Nursing', 3424 => 'Pharmacy', 4864 => 'Therapy', 6688 => 'Occupational therapy',
            7072 => 'Physiotherapy', 3787 => 'Veterinary medicine', 4832 => 'Humanities', 1438 => 'Archeology',
            8796 => 'History', 7210 => 'Linguistics & Literature (LL)', 9557 => 'Classical European languages',
            9391 => 'English LL', 9472 => 'French LL', 4391 => 'German LL', 3468 => 'Italian LL', 7408 => 'Linguistics',
            6230 => 'Other modern European languages', 5676 => 'Other non-European languages',
            5424 => 'Rhaeto-Romanic LL', 7599 => 'Translation studies', 7258 => 'Musicology', 4761 => 'Philosophy',
            3867 => 'Theology', 6527 => 'General theology', 5633 => 'Protestant theology',
            9787 => 'Roman catholic theology', 5889 => 'Interdisciplinary & Other',
            6059 => 'Information & documentation', 5561 => 'Military sciences', 8683 => 'Sport', 1861 => 'Law',
            4890 => 'Business law', 2990 => 'Natural sciences & Mathematics', 8990 => 'Astronomy', 4195 => 'Biology',
            7793 => 'Ecology', 6451 => 'Chemistry', 1266 => 'Computer science', 5255 => 'Earth Sciences',
            7950 => 'Geography', 2158 => 'Mathematics', 6986 => 'Physics', 8637 => 'Social sciences',
            9619 => 'Communication and media studies', 8367 => 'Ethnology', 1774 => 'Gender studies',
            1514 => 'Political science', 6005 => 'Psychology', 7288 => 'Social work', 6525 => 'Sociology',
            9321 => 'Technology & Applied sciences', 3624 => 'Agriculture', 1442 => 'Enology', 1892 => 'Biotechnology',
            7132 => 'Building Engineering', 5727 => 'Chemical Engineering', 9389 => 'Construction Science',
            2527 => 'Civil Engineering', 9738 => 'Rural Engineering and Surveying', 5742 => 'Electrical Engineering',
            2850 => 'Environmental Engineering', 9768 => 'Food technology', 2979 => 'Forestry',
            1566 => 'Material sciences', 8189 => 'Mechanical Engineering', 5324 => 'Automoive Engineering',
            8502 => 'Microtechnology', 4380 => 'Production and Enterprise', 7303 => 'Telecommunication',
    ];
}

?>
