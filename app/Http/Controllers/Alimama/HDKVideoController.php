<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Entitys\App\Collection;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HDKVideoController extends Controller
{
    private $api_key = "Licieuh";
    private $vip_percent = 0.325;
    private $common_percent = 0.2;
    private $is_open = 0;
    private $share_vip_percent = 0.1;
    private $share_common_percent = 0.05;
    private $name = 'woxiaoli675015017';
    private $mm = 'mm_122930784_46170255_91593200288';
    private $new_api_key = "Licieuh";
    private $new_name = 'woxiaoli675015017';
    private $new_mm = 'mm_122930784_46170255_109375250125';

    private $new_can_change_auth_info = [
        //woxiaoli
        '109375250125' => [
            'api_key' => 'Licieuh',
            'name' => 'woxiaoli675015017',
            'mm' => 'mm_122930784_46170255_109375250125',
        ],
        '109469450037' => [ # 卢淑清13799401629
            'api_key' => 'putaolushuqing',
            'name' => '卢淑清13799401629',
            'mm' => 'mm_123640184_378350331_109469450037',
        ],
        '109467850460' => [ # 徐丽华15959875125
            'api_key' => 'putaoxulihua',
            'name' => 'xxh4353',
            'mm' => 'mm_123348922_46184097_109467850460',
        ],
        '109467900491' => [ # 黄雪惠
            'api_key' => 'putaohuangxh',
            'name' => '雪惠000',
            'mm' => 'mm_105946111_379150017_109467900491',
        ],
        '109551050001' => [# 爱隔千里
            'api_key' => 'aigeqianli',
            'name' => '爱隔千里',
            'mm' => 'mm_123184147_379150041_109551050001',
        ]
    ];


    /*
     * get msg
     */
    public function getListMsg()
    {

        $nike_list = ['小***女', '悦***目', '缘***空', '明***好', '与***进', '稳***人', '落***雨', '离***归', '话***者', '仰***头', '春***吹', '世***仙', '一***求', '几***欢', '超***你', '少***事', '方***光', '甜***标', '你***吧', '安***欢', '治***女', '巴***雨', '微***脸', '爱***罕', '掌***花', '林***绿', '如***果', '容***浅', '一***雪', '丹***纯', '柠***乖', '野***馥', '九***莉', '无***衷', '青***你', '不***i', '彩***辉', '宾***归', '痛***谈', '开***饮', '为***弃', '脱***袖', '天***汤', '感***忱', '赞***宏', '恭***财', '给***财', '招***铛', '招***', '咱***吗', '长***糖', '嘻***', '缘***缘', '蜜***客', '万***兴', '同***缘', '巧***', '味***王', '乐***事', '吉***意', '旺***福', '大***到', '爱***海', '吉***祥', '三***缘', '好***铺', '臻***婚', '德***备', '继***力', '前***限', '双***门', '锦***程', '师***怀', '诲***倦', '嘉***子', '遗***福', '一***由', '虚***势', '须***力', '小***i', '小***范', '相***念', '勿***安', '無***痛', '没***同', '时***心', '生***合', '任***产', '钱***迷', '前***限', '男***财', '莫***媳', '觅***森', '吝***鬼', '利***心', '励***师', '锦***程'];

        $real_list = ['付*香', '黄*明', '俞*', '王*荣', '王*', '鞠*霞', '杨*', '林*明', '杨*', '黄*基', '张*贞', '陈*武', '赵*勤', '刘*洁', '刘*琳', '熊*龙', '贾*香', '李*芳', '魏*欣', '董*文', '丁*莉', '王*', '何*燕', '黄*海', '吴*潘', '李*恩', '方*燕', '罗*萍', '李*珍', '刘*海', '刘*', '周*萍', '张*平', '肖*', '封*浪', '孙*萍', '李*红', '邓*美', '吴*', '魏*民', '徐*', '郭*美', '蒋*', '郭*红', '杨*', '张*红', '郭*杰', '栾*靖', '张*英', '冯*宇', '陈*涛', '罗*珍', '尹*民', '吴*春', '刘*秀', '杨*松', '胡*平', '胡*伟', '王*莲', '钟*敏', '丘*伟', '姜*园', '何*', '叶*华', '曹*平', '李*文', '陈*', '王*', '李*将', '杨*峰', '闫*', '刘*', '桂*芬', '尹*迁', '张*', '张*勇', '伏*娜', '郭*雅', '陈*来', '王*', '李*琴', '李*华', '赵*', '莫*福', '张*', '刘*俊', '郁*', '徐*', '魏*坤', '胡*义', '才*', '凌*', '郭*良', '庞*阳', '徐*凤', '李*香', '吴*宁', '卢*江', '张*博', '曾*', '赵*环', '孙*英', '李*辉', '罗*存', '王*章', '王*敏', '陈*丹', '李*芝', '张*军', '姚*军', '葛*群', '赵*强', '吴*桂', '郝*风', '王*英', '龙*涛', '李*梅', '孔*细', '刘*红', '苏*君', '杨*淳', '吴*', '史*成', '方*同', '莫*智', '张*梅', '郭*丽', '唐*', '沈*飞', '林*', '李*', '李*贞', '王*电', '张*莲', '沙*亮', '李*铭', '黄*洪', '龚*明', '陈*旋', '张*礼', '曹*梅', '徐*骙', '孟*鑫', '邱*忠', '雷*灿', '丁*波', '周*', '杨*', '马*兰', '杨*', '梁*彬', '李*蓉', '陈*双', '钱*成', '刘*健', '张*', '刘*亮', '杨*', '王*博', '谢*辉', '贾*斌', '杨*平', '韦*燕', '刘*艳', '彭*', '荣*萍', '魏*琴', '卞*爱', '雷*花', '黄*艳', '但*兵', '张*岐', '邓*金', '王*海', '赵*秀', '陈*艳', '陈*', '谭*勇', '闫*香', '吕*纹', '王*刚', '余*祥', '陈*', '李*', '朱*凤', '张*兰', '高*', '周*刚', '岳*英', '聂*兰', '龚*容', '田*生', '崔*晶', '刘*香', '龙*豪', '吴*云', '翟*权', '叶*美', '孙*庆', '纪*', '伍*超', '黄*欣', '孙*铅', '黄*博', '李*民', '龙*杰', '贺*改', '郜*新', '杨*珍', '李*伟', '陆*吕', '李*技', '陆*鹏', '尹*宝', '蒙*予', '江*红', '李*', '刘*奎', '林*媛', '张*', '金*波', '辛*刚', '周*', '龙*德', '胡*能', '杨*权', '陈*英', '王*', '粟*和', '刘*强', '龙*学', '林*玉', '欧*旅', '陈*萍', '边*琪', '陆*明', '石*肖', '彭*菖', '汪*香', '曾*梅', '石*', '许*华', '成*志', '潘*升', '唐*', '朱*君', '李*玲', '隋*庆', '李*', '粟*鹏', '姚*', '高*清', '吕*跃', '王*芳', '粟*', '李*燕', '张*华', '吴*会', '王*山', '林*婷', '朱*', '王*飞', '王*琴', '施*媛', '梁*聪', '李*华', '于*兰', '何*阳', '吕*山', '杨*建', '张*娇', '康*凡', '田*江', '于*军', '裴*军', '王*护', '苏*明', '李*桥', '陈*伟', '李*凤', '孙*泓', '孟*亮', '郭*龙', '王*分', '刘*', '张*香', '王*玲', '李*艳', '罗*美', '高*', '龙*', '王*娥', '邹*双', '龚*', '李*展', '陈*连', '马*林', '林*贞', '郑*旸', '魏*娟', '李*', '高*', '彭*', '李*云', '黄*', '粟*淑', '吴*宇', '甄*英', '林*成', '王*山', '王*平', '余*珍', '高*', '孟*阳', '吴*凯', '李*儒', '孙*马', '梁*理', '曾*', '李*香', '吴*聪', '汉*爱', '黄*珍', '母*伟', '仝*塬', '李*莲', '程*俊', '王*强', '韩*', '刘*', '郭*旺', '罗*红', '熊*群', '贾*凤', '代*芳', '黄*', '郑*', '杨*刚', '王*斌', '朱*现', '黄*玲', '吴*珍', '杨*睿', '徐*锋', '李*', '马*珍', '郁*小', '齐*远', '刘*', '姚*', '陈*群', '葛*香', '周*平', '王*明', '梁*园', '刘*岩', '王*龙', '徐*兰', '李*峰', '唐*琼', '范*艳', '杨*美', '吕*', '贺*林', '韩*连', '池*红', '李*焕', '刘*平', '孙*', '郭*宇', '刘*伟', '翟*强', '张*军', '杨*华', '王*文', '李*', '潘*燕', '张*', '杨*国', '石*刚', '孙*玲', '吴*容', '李*', '林*青', '王*阳', '雷*艳', '林*金', '王*转', '叶*兰', '付*梅', '李*玫', '董*蛟', '许*琴', '李*', '王*贤', '赵*敏', '余*林', '王*梅', '米*涣', '杨*宏', '张*琼', '赵*会', '魏*', '赵*', '李*连', '刘*伟', '章*兵', '臧*年', '魏*宣', '赵*亭', '王*科', '谢*云', '蒋*芳', '景*谰', '米*军', '段*发', '黄*霞', '尹*丹', '庞*霞', '谢*', '徐*花', '张*福', '母*贤', '田*灵', '李*民', '杨*波', '胡*玲', '赖*先', '张*美', '张*', '王*', '李*韬', '耿*秀', '李*丽', '杨*梅', '罗*', '李*', '龚*山', '王*军', '吴*辉', '井*宝', '吴*兰', '龚*敏', '徐*宁', '刘*侠', '张*雷', '何*普', '张*博', '陈*宇', '龚*华', '罗*刚', '赵*', '王*勇', '张*海', '王*兰', '罗*仁', '吉*媛', '陈*辉', '杨*', '田*红', '李*泽', '张*华', '张*梁', '戈*', '很*心', '卫*', '李*荣', '杨*彦', '刘*连', '林*', '张*超', '刘*珍', '向*丽', '刘*国', '王*权', '于*秀', '黄*成', '路*', '黄*连', '李*宁', '何*学', '康*慧', '林*平', '舒*寿', '殷*河', '刘*元', '韩*萍', '张*兰', '蒲*灵', '李*红', '姜*茹', '修*苓', '刘*青', '陈*庭', '刘*芬', '胡*龙', '朱*收', '石*琴', '赵*艳', '邓*龙', '史*荣', '周*有', '陈*荣', '刘*玲', '杨*平', '黄*兰', '党*委', '杨*隧', '郭*琦', '胡*', '王*波', '姜*', '胡*妹', '程*波', '陈*青', '徐*雄', '卿*勇', '阎*红', '冀*华', '宋*兄', '郭*雪', '任*雄', '姚*江', '解*科', '徐*峰', '曹*盛', '荆*云', '于*', '纪*敏', '陶*央', '李*之', '刘*梅', '牛*勋', '宋*焕', '宋*国', '杨*', '朱*兰', '李*霞', '周*华', '刘*英', '丛*莲', '姜*娥', '陈*玉', '蓝*锋', '王*', '夏*平', '孙*坡', '石*欣', '潘*丰', '张*成', '曹*静', '王*梅', '马*勤', '蒙*强', '马*成', '樊*芳', '李*珍', '李*梅', '温*云', '李*根', '李*辉', '屈*民', '黄*寿', '马*远', '葛*', '赵*', '李*', '陈*红', '张*', '徐*权', '刘*举', '潘*明', '赵*学', '周*仙', '徐*平', '朱*侠', '唐*菊', '李*铭', '杨*东', '赵*镭', '马*', '吕*芬', '吴*霞', '王*英', '邓*东', '张*芬', '夏*宇', '王*堂', '罗*山', '王*阳', '谷*楠', '洪*怀', '李*灿', '乔*', '赵*清', '王*艳', '张*杰', '李*', '龙*雄', '张*生', '周*', '张*志', '孙*', '马*萍', '王*保', '杜*伦', '许*苗', '阿*拜', '徐*平', '李*田', '赵*玲', '王*', '李*利', '胡*博', '李*林', '曹*明', '张*洋', '郭*标', '马*平', '张*玲', '曹*', '陈*学', '苏*英', '徐*平', '吕*波', '何*芹', '曹*华', '李*芳', '彭*屏', '罗*琴', '何*军', '南*荣', '惠*智', '李*文', '廖*', '孟*军', '汪*华', '侯*', '张*英', '王*义', '范*', '蒙*珠', '高*玲', '文*燕', '师*琳', '徐*平', '赵*峰', '石*', '方*兵', '宋*生', '刘*玉', '任*娥', '孙*丽', '耿*琴', '秦*娟', '李*', '郑*珍', '李*平', '于*', '肖*红', '张*', '刘*妹', '朱*君', '彭*', '向*华', '孙*霞', '郭*淼', '黄*芳', '宗*君', '杨*和', '宋*林', '郭*升', '赵*飞', '曹*全', '马*', '刘*全', '舒*朋', '蔡*军', '周*权', '朱*华', '钱*丰', '陈*', '季*尚', '徐*佳', '王*霖', '韦*庆', '曹*莲', '杨*', '郭*', '黄*一', '彭*菊', '李*阳', '唐*男', '邬*军', '孙*', '朱*林', '任*文', '吴*丽', '廉*岩', '凌*', '尹*善', '罗*书', '许*方', '张*芬', '庭*香', '周*春', '闫*', '覃*万', '由*娜', '李*敏', '田*梅', '任*碧', '张*辉', '刘*英', '李*驰', '韩*花', '郭*生', '周*权', '白*霞', '宫*新', '陈*梅', '董*慧', '乔*本', '郭*梅', '罗*全', '严*霞', '陈*华', '李*林', '肖*丽', '刘*香', '郑*', '王*军', '刘*军', '陈*荣', '李*华', '金*珍', '巩*瑞', '付*清', '杨*媛', '王*丽', '卿*玲', '王*民', '周*荣', '黄*', '齐*红', '肖*英', '赵*平', '吴*青', '卢*', '梁*旭', '张*华', '王*会', '车*', '高*岗', '郭*英', '蒙*刚', '谢*光', '唐*萍', '王*锋', '贾*', '解*丞', '谢*文', '宋*奎', '罗*华', '贺*友', '郭*利', '吴*晴', '姚*玲', '蔡*忠', '何*川', '李*飞', '刘*伟', '魏*兵', '莫*萍', '刘*凤', '李*良', '周*成', '张*玉', '王*荣', '赵*梅', '彭*兴', '王*红', '廖*兰', '苏*德', '邹*鑫', '杨*珍', '肖*娣', '李*', '崔*庆', '张*春', '张*民', '李*杰', '蓝*锋', '孟*挺', '孙*华', '阳*明', '苗*玲', '邹*龙', '荣*新', '李*家', '张*根', '张*平', '徐*辉', '陈*兰', '张*梅', '贺*丁', '王*敏', '曹*仙', '庞*添', '宋*军', '李*兰', '赵*宇', '张*征', '李*', '杨*燕', '张*军', '何*昌', '张*洁', '何*霞', '欧*香', '尹*虎', '梁*静', '张*', '刘*如', '许*巧', '石*强', '周*顺', '蒙*妹', '邢*武', '朱*芝', '李*林', '蒋*理', '王*访', '蔡*琴', '李*婷', '王*宝', '郝*华', '蔡*枝', '王*平', '马*琴', '罗*安', '罗*龙', '姜*', '高*霞', '胡*春', '刘*平', '王*云', '龙*勇', '刘*明', '曾*雨', '刘*秀', '郎*杰', '吴*梅', '李*梅', '张*梅', '李*', '仲*勤', '梅*荣', '姜*阳', '张*利', '何*珍', '陈*楠', '袁*歌', '冠*荣', '包*孟', '苏*平', '李*英', '孙*华', '蒋*香', '邹*芬', '居*力', '吴*艳', '马*静', '蔡*忠', '赖*男', '王*良', '杜*', '李*线', '潘*平', '玛*克', '王*兰', '郭*洋', '费*晶', '谢*', '谭*锦', '董*芳', '陈*辉', '王*杰', '何*琴', '易*云', '于*贤', '赵*', '许*玲', '陈*红', '蕉*仙', '姜*杰', '王*友', '赵*利', '袁*云', '邵*利', '刘*', '龚*强', '张*军', '李*蓉', '高*芳', '彭*文', '余*伟', '孙*平', '郑*华', '韩*华', '李*群', '李*生', '肖*红', '俞*凤', '马*买', '尼*勋', '薛*', '马*芝', '夏*', '李*', '吴*珍', '陈*芳', '彭*英', '雷*', '张*', '阿*义', '陶*', '蒋*琪', '龚*辉', '高*博', '汪*林', '徐*平', '杨*红', '陈*', '颜*宏', '龚*雷', '田*平', '曾*峰', '张*玲', '张*连', '左*绍', '赵*', '王*', '贾*超', '赵*梅', '吴*顺', '蒋*茹', '于*英', '闫*美', '李*军', '王*', '赵*智', '张*', '周*梅', '刘*芬', '王*玲', '邹*', '岳*保', '熊*平', '邱*毅', '钟*', '朱*兴', '钱*胜', '范*伦', '蒋*涛', '郭*玲', '谢*岗', '贾*玉', '宋*霞', '张*君', '乌*图', '左*绍', '纪*军', '高*国', '朱*才', '张*华', '黄*博', '周*艳', '吴*秋', '马*华', '鲍*成', '谢*尔', '张*英', '雷*芳', '梁*芝', '胡*真', '陈*莲', '金*一', '刘*泽', '初*', '李*兴', '苏*', '孔*连', '马*', '李*廷', '张*', '宋*胜', '杨*', '徐*华', '刘*宏'];

        //$phone_list = ['187****1523', '186****7532', '186****4034', '166****2945', '158****0798', '159****8078', '183****6122', '152****8508', '177****0848', '152****3755', '155****4807', '137****3151', '156****4605', '139****6361', '186****9199', '166****9756', '139****4975', '156****3496', '138****2641', '185****9079', '135****8196', '134****8219', '181****6963', '152****2313', '151****2657', '133****5348', '183****6028', '130****6758', '153****7083', '189****0508', '139****8755', '182****5998', '138****4522', '137****3201', '166****9337', '138****6538', '131****7383', '136****3795', '177****4234', '138****0147', '135****5200', '147****4519', '159****9957', '138****3587', '156****1851', '151****2628', '155****5993', '137****5783', '136****9112', '150****7762', '151****8642', '137****2641', '181****9399', '137****8938', '159****8097', '173****8275', '155****5885', '151****5076', '182****5193', '135****2908', '135****9182', '139****6686', '135****1775', '150****1952', '189****8863', '155****5064', '134****3747', '186****7459', '151****6963', '138****4226', '156****9914', '182****2563', '158****7071', '150****5321', '130****2893', '150****2271', '152****0551', '139****5617', '176****4676', '138****5567', '139****8769', '181****9930', '151****6344', '158****4183', '187****2559', '181****1100', '156****6348', '158****6378', '136****2978', '151****8835', '151****7409', '134****1026', '130****9671', '151****7535', '155****6426', '155****3470', '183****8153', '152****0415', '135****0438', '132****9666', '183****8600', '150****5445', '139****5936', '176****0686', '187****8936', '176****1175', '152****8454', '186****0897', '151****3276', '159****7956', '139****8325', '152****3637', '137****4286', '151****3717', '185****2016', '184****1713', '158****5852', '133****7606', '182****2231', '150****5244', '155****6598', '130****1575', '176****2946', '151****5455', '189****4440', '188****2607', '138****0858', '152****9037', '152****9070', '135****2392', '182****0107', '199****8202', '136****0671', '180****5987', '150****9509', '133****0867', '159****0499', '135****2124', '180****7313', '180****6103', '151****9469', '152****0726', '152****6086', '183****4208', '184****3878', '133****9275', '130****7080', '184****2786', '155****5393', '147****9924', '150****8427', '134****4778', '139****4982', '157****9689', '185****0234', '188****6823', '137****4832', '198****6818', '158****0270', '181****6532', '182****9971', '156****6918', '132****2576', '138****2331', '177****2681', '139****0132', '186****6281', '153****8488', '152****1096', '186****3516', '151****0333', '134****4013', '137****3565', '186****3049', '186****4858', '138****4082', '137****1745', '136****5185', '150****9841', '159****4561', '183****8346', '158****1485', '134****8863', '177****6286', '135****5132', '131****8087', '189****8516', '130****1982', '159****8243', '155****8696', '136****5039', '151****6655', '138****5404', '139****9946', '135****1602', '189****8681', '136****4671', '159****7879', '133****3605', '153****3167', '184****9017', '152****7761', '153****6647', '135****6132', '175****7228', '183****8127', '136****8470', '139****8778', '199****6581', '152****6928', '139****8517', '180****9777', '159****2088', '184****7805', '181****9259', '175****9238', '132****2751', '130****9105', '189****0976', '132****7163', '137****9599', '151****7032', '151****3168', '137****1554', '184****3999', '183****9801', '151****1188', '184****6110', '150****8054', '131****9201', '189****6726', '151****0089', '185****0156', '182****2399', '199****9398', '182****0877', '134****6122', '181****1730', '150****1610', '135****0539', '132****7717', '176****4871', '183****1601', '131****5411', '180****2882', '138****3203', '180****5989', '135****5034', '150****3678', '131****4913', '136****2901', '186****1396', '137****3080', '139****7701', '134****8971', '138****7282', '152****6848', '187****0985', '182****1181', '139****9769', '136****0699', '150****2494', '151****1782', '150****9301', '185****4097', '133****6283', '135****8579', '130****3508', '137****7337', '156****6728', '153****0517', '187****4909', '138****2775', '139****9993', '137****4938', '159****6600', '159****9522', '183****0228', '187****2473', '176****9216', '139****2856', '138****2111', '186****4741', '182****9099', '150****4613', '153****1291', '183****6356', '182****5761', '138****4656', '139****4354', '136****5229', '138****2783', '185****2005', '158****7389', '187****8001', '158****4359', '186****2787', '158****0797', '139****0318', '157****3733', '135****4408', '135****0723', '150****0006', '183****7997', '186****2816', '151****5033', '159****9500', '151****8961', '132****9147', '135****8857', '152****6460', '133****1534', '156****2937', '132****1083', '187****5757', '138****2825', '182****0111', '135****5758', '150****5723', '138****5998', '150****0991', '158****5865', '187****8039', '180****3270', '150****5822', '130****7119', '139****7956', '177****7226', '139****1396', '189****9176', '133****2746', '139****1129', '187****6181', '150****2620', '138****8775', '151****2721', '138****8711', '131****6473', '132****1608', '137****8551', '131****0517', '158****5950', '159****6385', '137****6772', '155****3501', '159****8101', '137****0168', '137****8606', '188****3319', '187****0736', '156****3651', '189****6681', '139****0236', '185****3900', '182****7100', '155****3068', '138****1231', '138****7280', '138****6039', '153****7620', '134****4632', '139****8714', '132****8656', '182****6100', '182****1532', '156****9381', '183****7508', '185****2470', '181****9004', '135****5386', '158****1283', '132****5862', '139****6102', '185****6242', '158****7259', '155****2359', '133****3230', '136****3185', '136****5788', '152****8925', '134****6300', '180****2205', '176****8429', '138****5824', '186****4015', '177****3623', '139****0192', '134****8537', '139****8900', '138****3098', '158****9529', '133****6548', '188****3383', '137****9406', '182****1576', '186****0195', '181****9547', '137****2504', '134****2170', '158****8958', '155****2626', '157****9324', '173****9699', '158****0104', '181****0338', '176****9385', '178****7155', '138****9797', '150****8096', '152****1681', '188****3031', '138****9145', '159****9528', '136****2939', '135****7092', '134****3935', '139****9985', '155****1112', '136****8305', '131****7286', '151****9781', '138****4177', '137****7285', '139****7363', '187****3831', '159****4401', '189****0690', '135****9653', '157****3082', '186****0161', '153****4543', '176****7635', '187****1939', '187****9725', '136****4373', '186****1097', '130****4676', '135****2659', '138****5160', '158****9860', '177****9683', '159****8434', '138****0762', '183****5851', '183****9572', '130****3735', '156****5799', '155****1736', '155****7853', '139****7320', '152****0556', '159****7141', '188****4097', '189****7283', '136****2844', '150****3395', '137****2736', '175****9816', '137****1501', '156****0258', '150****2746', '134****0031', '153****5925', '150****4675', '188****5602', '173****0872', '136****1291', '187****4505', '180****5276', '156****9643', '152****3963', '159****0096', '131****5078', '186****6331', '151****8022', '151****6098', '181****6158', '134****1388', '150****6697', '186****0321', '132****3559', '185****6053', '132****6937', '183****4849', '173****1800', '183****8890', '133****8389', '159****4927', '182****7638', '130****1619', '176****3898', '150****7651', '158****7012', '137****9758', '199****5816', '150****3324', '158****7183', '130****3428', '136****9617', '155****6715', '133****9671', '153****6376', '153****2965', '156****1869', '135****1845', '138****5003', '138****8169', '139****5537', '188****1743', '185****1968', '152****7914', '134****6702', '151****9316', '155****1392', '133****8192', '176****1123', '130****3256', '185****1212', '187****8402', '150****1187', '185****3718', '151****6247', '176****2076', '150****2688', '178****1235', '150****6213', '134****7860', '186****9332', '136****9950', '176****6691', '156****5853', '178****4123', '134****7664', '177****3349', '150****8729', '185****7093', '138****0567', '152****0697', '131****1366', '131****0163', '136****8839', '158****3168', '175****3715', '187****0505', '135****1620', '153****6971', '185****3719', '159****1128', '181****3797', '137****0709', '177****7893', '185****3966', '155****9909', '186****7060', '150****3088', '138****1137', '133****6352', '138****4329', '136****6094', '186****1393', '150****1610', '137****5922', '176****3039', '156****9341', '188****4560', '139****8114', '158****0447', '159****5998', '132****1915', '187****0976', '173****0034', '157****1135', '181****9760', '130****3337', '158****5188', '135****5367', '131****5164', '138****6794', '153****8638', '189****1106', '130****9796', '176****3905', '132****9157', '158****6414', '136****1531', '151****9302', '130****6666', '138****9658', '159****2033', '181****5696', '150****6447', '182****4482', '135****3936', '186****1373', '188****8125', '189****6590', '159****3104', '189****5220', '181****7072', '138****1343', '130****6920', '136****9795', '150****7094', '132****4945', '159****7782', '135****3426', '135****1696', '136****4301', '133****6393', '152****5846', '158****4558', '151****3189', '150****1511', '188****6277', '158****3423', '187****9095', '182****5666', '136****4956', '139****1183', '137****9412', '151****4189', '159****4989', '157****2486', '182****0768', '183****3509', '183****8181', '151****1525', '158****8654', '151****3092', '151****1302', '135****3108', '186****1308', '159****5589', '150****0348', '137****8501', '189****9136', '159****2425', '135****7575', '159****0771', '136****0488', '180****5689', '135****7367', '166****9651', '135****8168', '134****4119', '188****9593', '180****4202', '131****4151', '188****0891', '185****8567', '152****2426', '133****3070', '159****2059', '137****2006', '133****3529', '138****1982', '137****8395', '166****6513', '139****2765', '135****4593', '138****1696', '156****7486', '183****8822', '137****2335', '181****8369', '184****3700', '183****4378', '151****4046', '157****2190', '155****5920', '176****5261', '150****8492', '185****6230', '166****5310', '187****6232', '183****7440', '180****6607', '137****3432', '135****1359', '155****8903', '138****0968', '152****4011', '180****8944', '151****7722', '158****0139', '185****1530', '189****1317', '139****6718', '159****3989', '158****9579', '131****0029', '137****2526', '138****8869', '138****6425', '133****6611', '188****6618', '136****6840', '183****8916', '150****5571', '139****4722', '181****1203', '186****6757', '139****2203', '137****8890', '182****1079', '166****5826', '131****4122', '134****9504', '180****7863', '182****7099', '138****3720', '136****1453', '132****5033', '189****8261', '156****3900', '155****2191', '139****3178', '178****7990', '187****2585', '139****9424', '133****5209', '139****3540', '181****8761', '176****4932', '158****5356', '182****5146', '132****4448', '132****5236', '134****1110', '155****3979', '156****4978', '139****4260', '158****8257', '150****7638', '138****8298', '159****9835', '175****7482', '136****1990', '137****2910', '185****5190', '173****5967', '178****0097', '176****2418', '139****1887', '186****7288', '158****0893', '158****2918', '137****5407', '186****1656', '150****6553', '151****5930', '183****2139', '182****1326', '130****8651', '159****5302', '186****4707', '136****7543', '152****0659', '138****2115', '185****2607', '188****9520', '176****3101', '135****3558', '150****6697', '132****6801', '152****3157', '138****0796', '139****0540', '186****8796', '155****8441', '138****6336', '139****6057', '185****6090', '156****2553', '152****3958', '137****4713', '159****0659', '139****9943', '187****6110', '152****3991', '187****0988', '186****7003', '139****9889', '139****0917', '138****2309', '150****9413', '139****8935', '138****8511', '152****4385', '182****9042', '156****0098', '189****8181', '158****3465', '132****4488', '151****6780', '138****9881', '151****4796', '182****2904', '159****3002', '150****0827', '157****0890', '147****8356', '180****3176', '150****0110', '159****8582', '130****5554', '134****8087', '186****9257', '182****7226', '182****8752', '137****1163', '151****7003', '131****6855', '159****1811', '189****5059', '138****6931', '130****6328', '187****4559', '173****4302', '133****1876', '188****9109', '176****3434', '151****2606', '155****9471', '180****2242', '186****4988', '136****9913', '152****8461', '131****2368', '152****4310', '136****8208', '135****6946', '176****0031', '166****8656', '133****8989', '136****8627', '166****5572', '187****1673', '137****8243', '135****8758', '138****6103', '187****6726', '182****3602', '156****8311', '150****7776', '185****4655', '189****8398', '155****7859', '159****1361', '182****0146', '133****5808', '182****1134', '185****2148', '139****1135', '187****5559', '152****2671', '156****3318', '133****1715', '158****4014', '134****4048', '136****2300', '150****1897', '138****5973', '153****1715', '137****6566', '189****9563', '138****3713', '155****1698', '139****1387', '189****0909', '183****2530', '152****3643', '187****4555', '136****2216', '131****6787', '131****8688', '139****5925', '186****4194', '133****8259', '136****1636', '173****0175', '130****5211', '187****2899', '136****7857', '171****4319', '199****2640', '176****8267', '159****1787', '132****2555', '153****3031', '130****8012', '130****2671', '182****2123', '133****0734', '185****5833', '159****1990', '136****0283', '137****2524', '159****8063', '133****9336', '135****4242', '176****0898', '158****5757', '187****1160', '151****0631', '151****6272', '134****2438', '186****6359', '176****1046', '133****3869', '131****4210', '186****9655', '133****6389', '176****8987', '187****3444', '138****0283', '177****8331', '138****4819', '176****3365', '189****9861', '137****2967', '152****6342', '173****1599', '158****7787', '187****3908', '185****8458', '181****8656', '151****2357', '157****0120', '138****3102', '139****9916', '166****8118', '159****4699', '158****5042', '130****8085', '151****7100', '159****3783', '188****4772', '137****8973', '139****7427', '180****5346', '130****0503', '182****1080', '199****1832', '133****1221', '134****1572', '157****7865', '156****5602', '173****1766', '134****1336', '180****7257', '176****2272', '133****7266', '136****7655', '136****4008', '184****9572', '177****0748', '198****5588', '186****9455', '139****5232', '135****4985', '131****6912', '138****3025', '180****6553', '186****9945', '187****0218', '187****9198', '139****7149', '150****5445', '185****7350', '159****5271', '159****5979', '158****6188', '133****7471', '187****7179', '185****0047', '186****1967', '155****3949', '151****0829', '187****8136', '150****0421', '158****3393', '156****7776', '159****6671', '139****5328', '151****9763', '136****3386', '158****7055', '189****0847', '189****8618', '166****8006', '139****9563', '182****6926', '133****8351', '187****3355', '138****6061', '189****9389', '139****9090', '133****1901', '156****5126'];

        $type_list = ['购买成功', '正在观看', '正在购买'];

        $nike_list_res = array_values(array_intersect_key($nike_list, array_flip(array_rand($nike_list, 30))));
        $real_list_res = array_values(array_intersect_key($real_list, array_flip(array_rand($real_list, 30))));
        //$phone_list_res = array_values(array_intersect_key($phone_list, array_flip(array_rand($phone_list, 20))));

        $all_res = array_merge($nike_list_res, $real_list_res);
        $msg_list = [];
        foreach ($all_res as $item) {
            $msg_list[] = [
                'name' => $item,
                'jump' => $type_list[array_rand($type_list)]
            ];
        }
        shuffle($msg_list);
        return $this->getResponse($msg_list);
    }

    /*
     * 高佣直转
     */
    public function getRatesUrl(Request $request, Client $client)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $high_commission_url = 'http://v2.api.haodanku.com/ratesurl';

        $post_api_data = [
            'apikey' => $this->api_key,
            'pid' => $this->mm,
            'tb_name' => $this->name,
            'itemid' => $itemid,
        ];
        $high_commission_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $res_high_commission_data = $client->request('POST', $high_commission_url, $high_commission_data);
        $json_res_high_commission_data = (string)$res_high_commission_data->getBody();
        $arr_high_commission = json_decode($json_res_high_commission_data, true);
        if ($arr_high_commission['code'] != 1) {
            return $this->getInfoResponse('1001', '该商品不存在');
        }

        if (@$arr_high_commission['data']['couponmoney'] == 0) {
            $good_url = @$arr_high_commission['data']['item_url'];
        } else {
            $good_url = @$arr_high_commission['data']['coupon_click_url'];
        }

        return $this->getResponse($good_url);
    }

    /*
    * 高佣直转 （2019年0904新版转链）
    */
    public function getNewRatesUrl(Request $request, Client $client, AlimamaInfo $alimamaInfo)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $app_id = $request->get('app_id');
        if (empty($app_id)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }

        $high_commission_url = 'http://v2.api.haodanku.com/ratesurl';

        $post_api_data = [
            'apikey' => $this->new_api_key,
            'pid' => $this->new_mm,
            'tb_name' => $this->new_name,
            'itemid' => $itemid,
        ];
        $high_commission_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $res_high_commission_data = $client->request('POST', $high_commission_url, $high_commission_data);
        $json_res_high_commission_data = (string)$res_high_commission_data->getBody();
        $arr_high_commission = json_decode($json_res_high_commission_data, true);
        if ($arr_high_commission['code'] != 1) {
            return $this->getInfoResponse('1001', '该商品不存在');
        }

        if (@$arr_high_commission['data']['couponmoney'] == 0) {
            $good_url = @$arr_high_commission['data']['item_url'];
        } else {
            $good_url = @$arr_high_commission['data']['coupon_click_url'];
        }

        $rid = $alimamaInfo->where('app_id', $app_id)->first();

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }

        if ($rid->adzone_id <> '109375250125') {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!！');
        }

        $good_url = $good_url . '&relationId=' . $rid->relation_id;

        if (empty($good_url)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($good_url);
    }


    /*
     * 兼容多样化账号
   * 高佣直转 （2019年10月29日新版转链）
   */
    public function getNewRatesUrlNew(Request $request, Client $client, AlimamaInfoNew $alimamaInfoNew)
    {
        $itemid = $request->get('itemid');
        if (empty($itemid)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }
        $app_id = $request->get('app_id');
        if (empty($app_id)) {
            throw new ApiException('缺少必要参数,错误信息', 3002);
        }

        $rid = $alimamaInfoNew->where('app_id', $app_id)->first();

        if (empty($rid)) {
            return $this->getInfoResponse('1002', '您未绑定淘宝账号!');
        }

        if (!empty($rid->adzone_id)) {
            $new_api_key = $this->new_can_change_auth_info[$rid->adzone_id]['api_key'];
            $new_name = $this->new_can_change_auth_info[$rid->adzone_id]['name'];
            $new_mm = $this->new_can_change_auth_info[$rid->adzone_id]['mm'];
        }

        if (empty($new_api_key)) {
            return $this->getInfoResponse('1002', '账号错误!请重试！');
        }

        if (empty($new_name)) {
            return $this->getInfoResponse('1002', '账号错误!请重试！');
        }

        if (empty($new_mm)) {
            return $this->getInfoResponse('1002', '账号错误!请重试！');
        }

        $high_commission_url = 'http://v2.api.haodanku.com/ratesurl';

        $post_api_data = [
            'apikey' => $new_api_key,
            'pid' => $new_mm,
            'tb_name' => $new_name,
            'itemid' => $itemid,
        ];
        $high_commission_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data
        ];
        $res_high_commission_data = $client->request('POST', $high_commission_url, $high_commission_data);
        $json_res_high_commission_data = (string)$res_high_commission_data->getBody();
        $arr_high_commission = json_decode($json_res_high_commission_data, true);
        if ($arr_high_commission['code'] != 1) {
            return $this->getInfoResponse('1001', '该商品不存在');
        }

        if (@$arr_high_commission['data']['couponmoney'] == 0) {
            $good_url = @$arr_high_commission['data']['item_url'];
        } else {
            $good_url = @$arr_high_commission['data']['coupon_click_url'];
        }


        $good_url = $good_url . '&relationId=' . $rid->relation_id;

        if (empty($good_url)) {
            return $this->getInfoResponse('1001', '网络开小差，请稍后再试');
        }

        return $this->getResponse($good_url);
    }
}
