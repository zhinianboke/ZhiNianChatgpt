<?php
/**
 * 网站访问密码验证
 *
 * @package ZhiNianChatgpt
 * @author 执念博客
 * @version 1.0.0
 * @link https://zhinianboke.com
 */
class ZhiNianChatgpt_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        
        Typecho_Plugin::factory('admin/write-post.php')->aiWrite = array('ZhiNianChatgpt_Plugin', 'render');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $div = new Typecho_Widget_Helper_Layout();
		$div->html('<small>		
			<h5>基础功能</h5>
			<span><p>①参数讲解：授权码请到 <a href="https://dy.zhinianboke.com" target="_BLANK">https://dy.zhinianboke.com</a> 申请；</p></span>
			<span><p>②本功能采用积分制，5积分一次</p></span>
			<span><p></p></span>
		</small>');
		$div->render();
		
        $cardId = new Typecho_Widget_Helper_Form_Element_Text('cardId', NULL, NULL,
        _t('授权码'), _t('请填写 授权码'));
        $form->addInput($cardId->addRule('required', _t('必须填写授权码')));
        
        $secretKey = new Typecho_Widget_Helper_Form_Element_Text('secretKey', NULL, NULL,
        _t('秘钥'), _t('请填写 秘钥'));
        $form->addInput($secretKey->addRule('required', _t('必须填写秘钥')));
        
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {
        $cardId = Helper::options()->plugin('ZhiNianChatgpt')->cardId;
        $secretKey = Helper::options()->plugin('ZhiNianChatgpt')->secretKey;
        echo <<<EOF
            <script type="text/javascript">
                // 显示遮罩
            	function coverDiv(){
                    var procbg = document.createElement("div"); //首先创建一个div
                    procbg.setAttribute("id","mybg"); //定义该div的id
                    procbg.style.background = "#000000";
                    procbg.style.width = "100%";
                    procbg.style.height = "100%";
                    procbg.style.position = "fixed";
                    procbg.style.top = "0";
                    procbg.style.left = "0";
                    procbg.style.zIndex = "500";
                    procbg.style.opacity = "0.6";
                    procbg.style.filter = "Alpha(opacity=70)";
                    document.body.appendChild(procbg);
                }
                //取消遮罩
                function hide() {
                    var body = document.getElementsByTagName("body");
                    var mybg = document.getElementById("mybg");
                    if(mybg) {
                        body[0].removeChild(mybg);
                    }
                }
                function showAiContent(){
                  if(document.getElementById("AiWriteAsk").style.display=="none"){
                      document.getElementById("AiWriteAsk").style.display="block"; 
                      document.getElementById("zhinianblog_respone_div").style.display="block";
                      document.getElementById("aiWrite-button").innerHTML="收起AI写作"; 
                  }
                  else{
                      document.getElementById("AiWriteAsk").style.display="none";
                      document.getElementById("zhinianblog_respone_div").style.display="none";
                      document.getElementById("aiWrite-button").innerHTML="展开AI写作"; 
                  }
               }
               function copyContent() {
                    const range = document.createRange();
                    range.selectNode(document.getElementById('zhinianblog_respone'));
                    const selection = window.getSelection();
                    if(selection.rangeCount > 0) selection.removeAllRanges();
                    selection.addRange(range);
                    document.execCommand('copy');
               }
               if(document.getElementById('copy')) {
                    document.getElementById('copy').addEventListener('click', copyArticle, false);
               }
               function startWrite() {
                    coverDiv();
                    document.getElementById("zhinianblog_respone").value = '等待接口响应中.......';
                    //创建异步对象  
                    var xhr = new XMLHttpRequest();
                    //设置请求的类型及url
                    var keywords = document.getElementById('keywords').value;
                    //post请求一定要添加请求头才行不然会报错
                    xhr.open('get', 'https://dy.zhinianboke.com/api/getChatgptAns?keywords='+keywords+'&cardId={$cardId}&secretKey={$secretKey}');
                    // xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=utf-8");
                    //发送请求
                    
                    xhr.send();
                    xhr.onreadystatechange = function () {
                        // 这步为判断服务器是否正确响应
                        if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText) {
                            var obj = JSON.parse(xhr.responseText);
                            var text = obj.data;
                            text = text.replaceAll('\\n\\n', '\\n')
                            document.getElementById("zhinianblog_respone").value = text;
                        }
                        hide();
                    };
               }
            </script>
            <div style="margin:0px 0px 10px 0px;text-align:left;">
                <a class="primary" id="aiWrite-button" style = "text-decoration:none; color:white; padding:7px; margin:17px 0px 17px 0px"onclick="showAiContent()">展开AI写作</a>
            </div>
            <div id ="AiWriteAsk" style="display:none;margin-top:10px;margin-bottom:10px;">
                <div>
                    <input type="text" placeholder="请输入关键字..." class="w-70 text title" id="keywords" value=""/>
                    <label onclick="startWrite()" class="primary" style = "text-decoration:none; color:white; padding:7px 15px 7px 15px; margin:17px 5px 17px 5px">开始生成</label>
                    <label onclick="copyContent()" class="primary" style = "text-decoration:none; color:white; padding:7px 15px 7px 15px; margin:17px 5px 17px 5px">复制内容</label>
                </div>   
            </div>
            <div id="zhinianblog_respone_div" style="display:none;">
                <textarea autocomplete="off" id="zhinianblog_respone" class="w-100 mono" rows="5">
                </textarea>
            </div>
EOF;
    }
    
    /**
     * 输出头部css
     * 
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function header() {
    }
    
    /**
     * 输出底部js
     * 
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function footer() {
        
    }
}