<?php
require_once('class.xoctCurl.php');
require_once('class.xoctCurl.php');

/**
 * Class xoctRequest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xoctRequest {

    /**
     * @param string $as_user
     *
     * @return mixed
     */
    public function get($as_user = '') {
        $url = $this->getUrl();

        $xoctCurl = new xoctCurl();
        $xoctCurl->setUrl($url);
        if ($as_user) {
            //			$xoctCurl->addHeader('X-API-AS-USER: ' . $as_user);
        }

        $xoctCurl->get();

        $responseBody = $xoctCurl->getResponseBody();

        return $responseBody;
    }

    /**
     * @param array  $post_data
     * @param string $as_user
     *
     * @return string
     */
    public function post(array $post_data, $as_user = '') {
        $xoctCurl = new xoctCurl();
        $xoctCurl->setUrl($this->getUrl());
        $xoctCurl->setPostFields($post_data);
        if ($as_user) {
            //			$xoctCurl->addHeader('X-API-AS-USER: ' . $as_user);
        }

        $xoctCurl->post();

        return $xoctCurl->getResponseBody();
    }

    /**
     * @param array  $post_data
     * @param string $as_user
     *
     * @return string
     */
    public function put(array $post_data, $as_user = '') {
        $xoctCurl = new xoctCurl();
        $xoctCurl->setUrl($this->getUrl());
        $xoctCurl->setPostFields($post_data);
        if ($as_user) {
            //			$xoctCurl->addHeader('X-API-AS-USER: ' . $as_user);
        }

        $xoctCurl->put();

        return $xoctCurl->getResponseBody();
    }

    /**
     *
     */
    public function delete() {
        // TODO implement here
    }

    /**
     * @return xoctRequest
     */
    public static function root() {
        return new self();
    }

    protected function __construct() {
    }

    const BRANCH_OTHER = -1;
    const BRANCH_SERIES = 1;
    const BRANCH_EVENTS = 2;
    const BRANCH_BASE = 3;
    /**
     * @var array
     */
    protected $parts = [];
    /**
     * @var int
     */
    protected $branch = self::BRANCH_OTHER;
    /**
     * @var string
     */
    //	protected $base = 'https://p2-int-api.cloud.switch.ch/api/';
    protected $base = 'https://cast-ng-test.switch.ch/api/';
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @return string
     */
    protected function getUrl() {
        $path = rtrim($this->getBase(), '/') . '/';
        $path .= implode('/', $this->parts);
        if ($this->getParameters()) {
            $path .= '?';
            foreach ($this->getParameters() as $k => $v) {
                $path .= $k . '=' . urlencode($v) . '&';
            }
        }

        return $path;
    }

    //
    // EVENTS
    //

    /**
     * @param string $identifier
     *
     * @return $this
     * @throws xoctExeption
     */
    public function events($identifier = '') {
        $this->checkRoot();
        $this->checkBranch([self::BRANCH_EVENTS]);
        $this->branch = self::BRANCH_EVENTS;
        $this->addPart('events');
        if ($identifier) {
            $this->addPart($identifier);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function publications($publication_id = '') {
        $this->checkBranch([self::BRANCH_EVENTS]);
        $this->addPart('publications');
        if ($publication_id) {
            $this->addPart($publication_id);
        }

        return $this;
    }


    //
    // SERIES
    //

    public function series($series_id = '') {
        $this->checkRoot();
        $this->checkBranch([self::BRANCH_SERIES]);
        $this->branch = self::BRANCH_SERIES;
        $this->addPart('series');
        if ($series_id) {
            $this->addPart($series_id);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function properties() {
        $this->checkBranch([self::BRANCH_SERIES, self::BRANCH_EVENTS]);
        $this->addPart('properties');

        return $this;
    }

    //
    // BOTH
    //

    /**
     * @return $this
     */
    public function metadata() {
        $this->checkBranch([self::BRANCH_SERIES, self::BRANCH_EVENTS]);
        $this->addPart('metadata');

        return $this;
    }

    /**
     * @return $this
     */
    public function acl() {
        $this->checkBranch([self::BRANCH_SERIES, self::BRANCH_EVENTS]);
        $this->addPart('acl');

        return $this;
    }

    //
    // BASE
    //

    public function base() {
        $this->checkBranch([self::BRANCH_BASE]);
        $this->checkRoot();
        $this->branch = self::BRANCH_BASE;

        return $this;
    }

    /**
     * @return $this
     */
    public function version() {
        $this->checkBranch([self::BRANCH_BASE]);
        $this->addPart('version');

        return $this;
    }

    /**
     * @return $this
     */
    public function organization() {
        $this->checkBranch([self::BRANCH_BASE]);
        $this->addPart('info');
        $this->addPart('organization');

        return $this;
    }

    //
    //
    //

    /**
     * @param $part
     */
    protected function addPart($part) {
        $this->parts[] = $part;
    }

    /**
     * @return array
     */
    public function getParts() {
        return $this->parts;
    }

    /**
     * @param array $parts
     */
    public function setParts($parts) {
        $this->parts = $parts;
    }

    /**
     * @return string
     */
    public function getBase() {
        return $this->base;
    }

    /**
     * @param string $base
     */
    public function setBase($base) {
        $this->base = $base;
    }

    /**
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function parameter($key, $value) {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * @return int
     */
    protected function getBranch() {
        return $this->branch;
    }

    /**
     * @param int $branch
     */
    protected function setBranch($branch) {
        $this->branch = $branch;
    }

    /**
     * @param array $supported_branches
     *
     * @throws xoctExeption
     */
    protected function checkBranch(array $supported_branches) {
        $supported_branches[] = self::BRANCH_OTHER;
        if (!in_array($this->branch, $supported_branches)) {
            throw new xoctExeption(xoctExeption::API_CALL_UNSUPPORTED);
        }
    }

    protected function checkRoot() {
        if (count($this->parts) > 0 OR $this->branch != self::BRANCH_OTHER) {
            throw new xoctExeption(xoctExeption::API_CALL_UNSUPPORTED);
        }
    }
}