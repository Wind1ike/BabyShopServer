<?php
  include_once('../../utils/head.php');

  $router->post(function($req, $res, $db, $util) {
    if(!$util->checkAuthorization($db)) {
      $res->send(403, '请先登陆后在进行操作');
      return ;
    }
    $params = $req['params'];

    $setParamsMsg = $util->isSetParams($params, ['name', 'float_price', 'preview', 'detail', 'bool_hot', 'bool_recomment', 'int_categoryId']);

    if($setParamsMsg['flag']) {
      $result = $db->insert('good', $params);
      if($result) {
        $res->send(200, '添加商品成功');
      } else {
        $res->send(400, '添加商品失败');
      }
    } else {
      $res->send(400, "添加商品失败, 缺少$setParamsMsg[key]参数");
    }
  });

  $router->get(function($req, $res, $db, $util) {
    $params = $req['params'];
    /** 获取商品详情 */
    $hasIdMsg = $util->isSetParams($params, ['int_id']);
    if($hasIdMsg['flag']) {
      $data = $db->select("SELECT * FROM good WHERE int_id = $params[int_id]");

      if($data) {
        $categoryId = $data[0]['categoryId'];
        $data[0]['categoryProperty'] =  $db->select("SELECT property FROM category WHERE int_id = $categoryId")[0]['property'];
        $res->send(200, '获取商品详情成功', $data[0]);
        return ;
      } else {
        $res->send(400, '没有该商品');
        return ;
      }
    }

    /** 获取热门列表 */
    $hasHotMsg = $util->isSetParams($params, ['bool_hot']);
    if($hasHotMsg['flag']) {
      $params['bool_hot'] = intval($params['bool_hot']);
      $data = $db->select("SELECT int_id, name, float_price, preview, int_categoryId FROM good WHERE bool_hot = $params[bool_hot]");

      if($data) {
        $res->send(200, '获取商品热门列表成功', array("list"=> $data));
        return ;
      } else {
        $res->send(200, '无热门列表商品', array("list"=> [], "total"=> 0));
        return ;
      }
    }

    /** 获取推荐列表 */
    $hasRecommentMsg = $util->isSetParams($params, ['bool_recomment']);
    if($hasRecommentMsg['flag']) {
      $params['bool_recomment'] = intval($params['bool_recomment']);
      $data = $db->select("SELECT int_id, name, float_price, preview, int_categoryId FROM good WHERE bool_recomment = $params[bool_recomment]");

      if($data) {
        $res->send(200, '获取商品推荐列表成功', array("list"=> $data));
        return ;
      } else {
        $res->send(200, '无推荐列表商品', array("list"=> [], "total"=> 0));
        return ;
      }
    }

    /** 获取商品分类列表 */
    $hasCategoryMsg = $util->isSetParams($params, ['int_categoryId']);
    if($hasCategoryMsg['flag']) {
      $params['int_categoryId'] = intval($params['int_categoryId']);
      $data = $db->select("SELECT int_id, name, float_price, preview, int_categoryId FROM good WHERE int_categoryId = $params[int_categoryId]");

      if($data) {
        $res->send(200, '获取商品分类列表成功', array("list"=> $data));
        return ;
      } else {
        $res->send(200, '该分类下无商品', array("list"=> [], "total"=> 0));
        return ;
      }
    }

    /** 获取商品列表 */
    $search = isSet($params['search']) ? $params['search'] : '';
    $selectCategory = $params['categoryId'] ? " AND int_categoryId = $params[categoryId] " : '';
    $page = $params['page'] ? $params['page'] : 1;
    $pageSize = $params['pageSize'];
    
    if($pageSize) {
      $start = intval($pageSize) * (intval($page) - 1);
      $data = $db->select("SELECT * FROM good WHERE name LIKE '%$search%' $selectCategory ORDER BY int_id DESC LIMIT $start, $pageSize");
    } else {
      $data = $db->select("SELECT * FROM good WHERE name LIKE '%$search%' $selectCategory ORDER BY int_id DESC");
    }

    foreach($data as $i => $good) {
      $categoryName = $db->select("SELECT name FROM category WHERE int_id = $good[categoryId]")[0]['name'];
      $data[$i]['categoryName'] = $categoryName;
    }

    $total = $db->count('good', "WHERE name LIKE '%$search%' $selectCategory");

    if($data) {
      $res->send(200, '获取商品列表成功', array("list"=> $data, "total"=> $total));
      return ;
    } else {
      $res->send(200, '无商品列表', array("list"=> [], "total"=> 0));
      return ;
    }
  });

  $router->put(function($req, $res, $db, $util) {
    if(!$util->checkAuthorization($db)) {
      $res->send(403, '请先登陆后在进行操作');
      return ;
    }
    $params = $req['params'];
    $setParamsMsg = $util->isSetParams($params, ['int_id']);

    if($setParamsMsg['flag']) {
      $result = $db->update('good', $params, "WHERE int_id = $params[int_id]");

      if($result) {
        $res->send(200, '更新商品信息成功');
      } else {
        $res->send(400, '更新商品信息失败');
      }
    } else {
      $res->send(400, "更新商品信息失败，缺少$setParamsMsg[key]参数");
    }
  });

  $router->delete(function($req, $res, $db, $util) {
    if(!$util->checkAuthorization($db)) {
      $res->send(403, '请先登陆后在进行操作');
      return ;
    }
    $params = $req['params'];
    $setParamsMsg = $util->isSetParams($params, ['int_id']);

    if($setParamsMsg['flag']) {
      $result = $db->delete('good', "WHERE int_id = $params[int_id]");

      if($result) {
        $res->send(200, '删除商品成功');
      } else {
        $res->send(400, '删除商品失败');
      }
    } else {
      $res->send(400, "删除商品失败，缺少$setParamsMsg[key]参数");
    }
  });
?>