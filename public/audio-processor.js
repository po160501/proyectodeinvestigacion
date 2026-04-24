// public/audio-processor.js
// AudioWorkletProcessor: corre en hilo separado, no se pausa al minimizar
class SoundMeterProcessor extends AudioWorkletProcessor {
    constructor() {
        super();
        this._buffer = [];
        this._interval = 128; // enviar cada N frames
        this._count = 0;
    }

    process(inputs) {
        const input = inputs[0];
        if (!input || !input[0]) return true;

        const samples = input[0];
        let sum = 0;
        for (let i = 0; i < samples.length; i++) sum += samples[i] * samples[i];
        const rms = Math.sqrt(sum / samples.length);

        this._buffer.push(rms);
        this._count++;

        if (this._count >= this._interval) {
            const avgRms = this._buffer.reduce((a, b) => a + b, 0) / this._buffer.length;
            this.port.postMessage({ rms: avgRms });
            this._buffer = [];
            this._count = 0;
        }
        return true; // mantener vivo
    }
}

registerProcessor('sound-meter-processor', SoundMeterProcessor);
