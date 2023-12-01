# CustomFileValidator
Также работает с виджетом kartik fileinput.

1) В виджет картика в pluginOptions добавить следующее:
```
'initialPreviewShowDelete'=>false,
'showRemove' => false,
```

2) В контроллере в нужном экшене перед $model->save() прописать CustomFileValidator::validate($model);
   По желанию, можно указать options и "кастомизировать" валидацию

Пример экшена

```
    public function actionCreate()
    {
        $model = new MainSlider();

        if ($model->load(Yii::$app->request->post())) {

            $options = [                                    <---------------------------- прописали тут
                'function' => function ($model) {
                    if ($model->mode == 1) {
                        return ['attributeToValidate' => ['image']];
                    } else {
                        return ['attributeToValidate' => ['video']];
                    }

                }
            ];

            CustomFileValidator::validate($model,$options); <---------------------------- прописали тут

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Запись успешно создана');
                if (isset($_POST['apply']))
                    return $this->redirect(['update', 'id' => $model->id]);
                elseif (isset($_POST['new']))
                    return $this->redirect(['create']);
                else
                    return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

```

При создании не даст создать с пустым файловым инпутом, а при обновлении нужно будет выбрать новый файл и нажать сохранить, чтобы заменить старый файл. 
