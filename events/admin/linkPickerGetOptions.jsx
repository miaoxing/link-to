import Icon from '@mxjs/icons';
import { useRef, useState } from 'react';
import {Modal} from 'antd';
import PropTypes from 'prop-types';
import {Form} from 'antd';
import {FormItem} from '@mxjs/a-form';

const UrlPicker = ({pickerRef, linkPicker, value}) => {
  const formRef = useRef();
  const [open, setOpen] = useState(true);

  // 每次都更新
  pickerRef && (pickerRef.current = {
    show: () => {
      setOpen(true);
    },
  });

  return <Modal
    title="填写链接"
    open={open}
    width={600}
    styles={{
      body: {
        paddingBlock: '.5rem',
      }
    }}
    onOk={() => {
      const url = formRef.current.getFieldValue('_url');
      if (url) {
        linkPicker.addValue({url});
      }
      setOpen(false);
    }}
    onCancel={() => {
      setOpen(false);
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
