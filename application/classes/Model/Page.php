<?php defined('SYSPATH') or die('No direct script access.');

class Model_Page extends Model_preDispatch
{

    public $id              = 0;
    public $type            = 0;
    public $status          = 0;
    public $id_parent       = 0;
    public $title           = '';
    public $content         = '';
    public $date            = '';
    public $is_menu_item    = 0;
    public $rich_view       = 0;
    public $dt_pin;
    public $uri             = '';
    public $author;
    public $parent;
    public $source_link     = '';

    public $attaches        = array();
    public $files           = array();
    public $images          = array();

    const TYPE_SITE_PAGE = 1;
    const TYPE_SITE_NEWS = 2;
    const TYPE_USER_PAGE = 3;

    const STATUS_SHOWING_PAGE = 0;
    const STATUS_HIDDEN_PAGE  = 1;
    const STATUS_REMOVED_PAGE = 2;

    public function __construct($id = 0)
    {
        if ( !$id ) return;

        $page = self::get($id);

        self::fillByRow($page);
    }

    private function fillByRow($page_row)
    {
        if (!empty($page_row))
        {

            foreach ($page_row as $field => $value) {
                if (property_exists($this, $field)) {
                    $this->$field = $value;
                }
            }

            $this->uri             = $this->getPageUri();
            $this->author          = new Model_User($page_row['author']);
        }

        return $this;
    }

    public function insert()
    {
        $page = Dao_Pages::insert()
                    ->set('type',           $this->type)
                    ->set('author',         $this->author->id)
                    ->set('id_parent',      $this->id_parent)
                    ->set('title',          $this->title)
                    ->set('content',        $this->content)
                    ->set('is_menu_item',   $this->is_menu_item)
                    ->set('rich_view',      $this->rich_view)
                    ->set('dt_pin',         $this->dt_pin)
                    ->set('source_link',    $this->source_link);

        if ($this->is_menu_item)
        {
            $page->clearcache('site_menu');
        }

        $page = $page->execute();

        if ($page)
        {
            return new Model_Page($page);
        }
    }

    public function update()
    {
        return Dao_Pages::update()
                    ->where('id', '=', $this->id)
                    ->set('id',             $this->id)
                    ->set('type',           $this->type)
                    ->set('status',         $this->status)
                    ->set('author',         $this->author->id)
                    ->set('id_parent',      $this->id_parent)
                    ->set('title',          $this->title)
                    ->set('content',        $this->content)
                    ->set('is_menu_item',   $this->is_menu_item)
                    ->set('rich_view',      $this->rich_view)
                    ->set('dt_pin',         $this->dt_pin)
                    ->set('source_link',    $this->source_link)
                    ->clearcache('page:' . $this->id, array('site_menu'))
                    ->execute();
    }

    public function setAsRemoved()
    {

        $this->status = self::STATUS_REMOVED_PAGE;
        $this->update();

        $childrens = $this->getChildrenPagesByParent($this->id);

        foreach ($childrens as $page) {
            $page->setAsRemoved();
        }

        return true;
    }

    public function get($id = 0)
    {
        return Dao_Pages::select()
                    ->where('id', '=', $id)
                    ->limit(1)
                    ->cached(Date::MINUTE * 30, 'page:' . $id)
                    ->execute();
    }

    public static function getPages( $type = 0, $limit = 0, $offset = 0, $status = 0, $pinned_news = false )
    {
        $pages_query = Dao_Pages::select()->where('status', '=', $status);

        if ($type)          $pages_query->where('type', '=', $type);
        if ($limit)         $pages_query->limit($limit);
        if ($offset)        $pages_query->offset($offset);
        if ($pinned_news)   $pages_query->order_by('dt_pin', 'DESC');

        $pages_rows = $pages_query->order_by('id','DESC')->execute();

        return self::rowsToModels($pages_rows);
    }

    public static function rowsToModels($page_rows)
    {
        $pages = array();

        if (!empty($page_rows))
        {
            foreach ($page_rows as $page_row)
            {
                $page = new Model_Page();

                $page->fillByRow($page_row);

                array_push($pages, $page);
            }
        }

        return $pages;
    }

    public static function getChildrenPagesByParent( $id_parent )
    {
        $query = Dao_Pages::select()
            ->where('status', '=', self::STATUS_SHOWING_PAGE)
            ->where('id_parent','=', $id_parent)
            ->order_by('id','ASC')
            ->execute();

        return self::rowsToModels($query);
    }

    public function getPageUri()
    {
        $title = $this->title;

        $title = Model_Methods::getUriByTitle($title);

        return strtolower($title);
    }

}