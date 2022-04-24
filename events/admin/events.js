export default {
  async onLinkPickerGetOptions500(options) {
    options.push(...(await import('./linkPickerGetOptions')).default);
  },
};
