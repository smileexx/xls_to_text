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
        $articles = $this->modelDict->getAllArticles();
        $data['articles'] = $this->view->render('elements/dictionary_articles.php', array('articles' => $articles ));
        $data['vendors'] = $this->modelDict->getAllVendors();
        $this->view->generate('_common.php', 'dictionary_articles_view.php', $data);
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

}