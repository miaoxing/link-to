import Icon from '@mxjs/icons';
import { useRef, useState } from 'react';
import {Modal} from 'antd';
import PropTypes from 'prop-types';
import {Form} from 'antd';
import {FormItem} from '@mxjs/a-form';

const UrlPicker = ({pickerRef, linkPicker, value}) => {
  const formRef = useRef();
  const [visible, setVisible] = useState(true);

  // 每次都更新
  pickerRef && (pickerRef.current = {
    show: () => {
      setVisible(true);
    },
  });

  return <Modal
    title="填写链接"
    visible={visible}
    width={600}
    bodyStyle={{
      padding: '1rem',
    }}
    onOk={() => {
      const url = formRef.current.getFieldValue('_url');
      if (url) {
        linkPicker.addValue({url});
      }
      setVisible(false);
    }}
    onCancel={() => {
      setVisible(false);
    }}
  >
    <Form ref={formRef} labelCol={{span: 6}} wrapperCol={{span: 14}}>
      <FormItem label="链接地址" name="_url" initialValue={value.url}/>
    </Form>
  </Modal>;
};

UrlPicker.propTypes = {
  pickerRef: PropTypes.object,
  linkPicker: PropTypes.object,
  value: PropTypes.object,
};

const UrlPickerLabel = ({value}) => {
  return value.url;
};

UrlPicker.Label = UrlPickerLabel;

export default [
  {
    value: 'url',
    label: <>链接 <Icon type="mi-popup"/></>,
    inputLabel: '链接',
    picker: UrlPicker,
    pickerLabel: UrlPicker.Label,
  },
];
