class SingleImageUpload {
  get options() {
    return {
      fileSelectCallback: null,
    };
  }
  constructor(element, options = {}) {
    this.settings = Object.assign(this.options, options);
    this.element = element;
    if (!element) {
      console.error('Single image upload element is not defined!');
      return;
    }
    this._init();
  }
  _init() {
    this.button = this.element.getElementsByTagName('button')[0];
    this.input = this.element.getElementsByTagName('input')[0];
    this.img = this.element.getElementsByTagName('img')[0];
    this.dropArea = this.element;
    this._addListeners();
  }
  _addListeners() {
    this.button && this.button.addEventListener('click', this._onSelectButtonClick.bind(this));
    this.input && this.input.addEventListener('change', this._onInputChange.bind(this));
    this.dropArea.addEventListener('dragover', this._onDragOver.bind(this));
    this.dropArea.addEventListener('dragleave', this._onDragLeave.bind(this));
    this.dropArea.addEventListener('drop', this._onDrop.bind(this));
  }
  _onSelectButtonClick(event) {
    this.input.dispatchEvent(new MouseEvent('click'));
  }
  _onInputChange(event) {
    this._fileSelected(this.input.files[0]);
  }
  _onDragOver(event) {
    event.preventDefault();
    this.dropArea.classList.add('drag-over');
  }
  _onDragLeave(event) {
    this.dropArea.classList.remove('drag-over');
  }
  _onDrop(event) {
    event.preventDefault();
    this.dropArea.classList.remove('drag-over');
    if (event.dataTransfer.files && event.dataTransfer.files.length > 0) {
      let file = event.dataTransfer.files[0];
      if (file.type.startsWith('image/')) {
        this._fileSelected(file);
      } else {
        console.warn('Sadece resim dosyaları yüklenebilir.');
      }
    }
  }
  _fileSelected(file) {
    if (file) {
      let reader = new FileReader();
      reader.onload = this._onFileLoad.bind(this);
      reader.readAsDataURL(file);
      let dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      this.input.files = dataTransfer.files;
    }
  }
  _onFileLoad(event) {
    this.img.setAttribute('src', event.target.result);
    if (this.settings.fileSelectCallback) {
      let file = this.getFile();
      this.settings.fileSelectCallback({ file: file, result: event.target.result });
    }
  }
  getFile() {
    return this.input.files[0];
  }
}
