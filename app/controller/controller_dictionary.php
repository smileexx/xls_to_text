<?php

class ControllerDictionary extends Controller
{
    private $modelDict = null;

    function __construct()
    {
        $this->modelDict = new Model_Dictionary();
        parent::__construct();
    }

    function index()
    {
        $data = [];
        $data['page_header'] = 'Словарь';
        $data['articles'] = $this->modelDict->getAllArticles();
        $data['vendors'] = $this->modelDict->getAllVendors( 'code_price' );
        $data['tab_articles'] = $this->view->render('elements/dictionary/articles.php', $data);
        $data['tab_vendors'] = $this->view->render('elements/dictionary/vendors.php', $data);

        $this->view->generate('_common.php', 'dictionary_tabs.php', $data);
        unset($vendors);
    }

    public function add_article(){
        $form = $_POST;
        $res = $this->modelDict->createArticle( $form['income_hash'], $form['vendor'], $form['product_id'] );
        if($res){
            $this->outputJson( ['success' => true] );
        } else {
            $this->outputJson( ['success' => false] );
        }
    }

    public function delete_article(){
        $res = $this->modelDict->deleteArticle($_POST['id']);
        $this->outputJson( ['success' => true] );
    }

    public function add_vendor(){
        $form = $_POST;
        $res = $this->modelDict->createVendor( $form['title'], $form['code_price'], $form['code_robins']);
        if($res){
            $this->outputJson( ['success' => true] );
        } else {
            $this->outputJson( ['success' => false] );
        }
    }

    public function delete_vendor(){
        $res = $this->modelDict->deleteVendor($_POST['id']);
        $this->outputJson( ['success' => true] );
    }

}