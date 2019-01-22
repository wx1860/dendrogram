<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/14 0014
 * Time: 下午 3:47
 */

namespace DenDroGram\ViewModel;

use DenDroGram\Helpers\Func;

class NestedSetViewModel extends ViewModel
{
    private $root = <<<EOF
<ul>%s</ul>
EOF;

    private $branch = <<<EOF
<ul style="display:%s">%s</ul>
EOF;

    private $leaf = <<<EOF
<li>
    <div data-v=%s data-sign=%d class="dendrogram-nested-branch">
            <a href="javascript:void(0);" class="dendrogram-tab">
                %s
             </a>
             <button class="dendrogram-button" href="javascript:void(0);">
                %s
             </button>
         <a href="#form" class="dendrogram-grow">
            %s   
         </a>
         <div class="clear_both"></div>
    </div>
    %s
</li>
EOF;

    private $leaf_apex = <<<EOF
<li>
    <div data-v=%s class="dendrogram-nested-branch">
         <a href="javascript:void(0);" class="dendrogram-ban">
            %s 
         </a>
             <button class="dendrogram-button" href="javascript:void(0);">
                %s
             </button>
         <a href="#form" class="dendrogram-grow">
            %s
         </a>
         <div class="clear_both"></div>
    </div>
</li>
EOF;

    private $form = <<<EOF
<div id="form">
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button"></button>
        <div class="uk-modal-header">
            <h2 class="uk-modal-title">Headline</h2>
        </div>
        <div class="uk-modal-body">
            %s
        </div>
        <div class="uk-modal-footer uk-text-right"> 
            <button class="uk-button uk-button-danger" type="button">删除</button>
            <button class="uk-button uk-button-primary" type="button">保存</button>
        </div>
    </div>
</div>
EOF;

    public function index($data,$sign,$column,$form_data)
    {
        $this->sign = $sign;
        $this->column = $column;
        $this->form_data = $form_data;

        if($this->sign){
            $this->branch = Func::firstSprintf($this->branch,'block');
        }else{
            $this->branch = Func::firstSprintf($this->branch,'none');
        }

        $this->makeTree($data,$tree);

        return $this->tree_view;
    }

    /**
     * @param $array
     * @param array $tree
     */
    private function makeTree(&$array, &$tree = [])
    {
        if(empty($array)){
            return;
        }

        if (empty($tree)) {
            $item = array_shift($array);
            $item['children'] = [];
            $tree[] = $item;
            if (empty($array)) {
                //no children
                $this->tree_view = sprintf($this->root,
                    sprintf($this->leaf_apex,Func::arrayToJsonString($item),(int)$this->sign,$this->icon['expand'],$this->makeColumn($item),$this->icon['grow'],''));
                return;
            } else {
                $this->tree_view = sprintf($this->root,
                    sprintf($this->leaf,Func::arrayToJsonString($item),(int)$this->sign,$this->icon['ban'],$this->makeColumn($item),$this->icon['grow'],$this->branch));
            }
        }

        foreach ($tree as &$branch) {
            $shoot = [];
            foreach ($array as $key => $value) {
                if (($branch['depth'] + 1) == $value['depth'] && $branch['left'] < $value['left'] && $branch['right'] > $value['left']) {
                    $value['children'] = [];
                    $branch['children'][] = $value;
                    unset($array[$key]);
                    if (!$this->hasChildren($value,$array)) {
                        //无子节点
                        $shoot[] = $this->makeBranch($value, false);
                    } else {
                        $shoot[] = $this->makeBranch($value);
                    }
                }
            }

            if (!empty($branch['children']) && $array) {
                $this->tree_view = Func::firstSprintf($this->tree_view, join('', $shoot));
                $this->makeTree($array, $branch['children']);
            } elseif (empty($branch['children'])) {
                return;
            } else {
                $this->tree_view = Func::firstSprintf($this->tree_view, join('', $shoot));
            }
        }
    }

    private function hasChildren($item,$data)
    {
        foreach ($data as $key => $value) {
            if(($item['depth'] + 1) == $value['depth'] && $item['left'] < $value['left'] && $item['right'] > $value['right']){
                return true;
            }
        }
        return false;
    }

    private function makeColumn($data)
    {
        $text = '<div class="text">%s</div>';
        $html = '';
        foreach ($this->column as $column){
            $html.=sprintf($text,isset($data[$column])?$data[$column]:'');
        }
        return $html;
    }

    /**
     * 枝
     * @param $data
     * @param bool $node
     * @return string
     */
    private function makeBranch($data, $node = true)
    {
        if ($node) {
            $left_button = $this->sign ? $this->icon['shrink'] : $this->icon['expand'];
            return sprintf($this->leaf, Func::arrayToJsonString($data),(int)$this->sign,$left_button, $this->makeColumn($data),$this->icon['grow'], $this->branch);
        }
        return sprintf($this->leaf_apex, Func::arrayToJsonString($data),$this->icon['ban'], $this->makeColumn($data),$this->icon['grow'], '');
    }
}