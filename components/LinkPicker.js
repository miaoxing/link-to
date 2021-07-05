import { useState, useEffect } from "react";
import {default as BaseLinkPicker} from '@mxjs/a-link-picker';
import {event} from '@mxjs/app';

const LinkPicker = (props) => {
  const [options, setOptions] = useState([]);

  // 加载选项
  useEffect(() => {
    (async () => {
      let options = [];
      await event.trigger('linkPickerGetOptions', [options]);
      setOptions(options);
    })();
  }, []);

  return (
    <BaseLinkPicker options={options} {...props}/>
  );
};

export default LinkPicker;
