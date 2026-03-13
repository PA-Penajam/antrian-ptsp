/**
 * Modul thermal printer untuk Epson TM-M30II via ePOS SDK.
 * Menggunakan ESC/POS native commands, bukan HTML rendering.
 */
window.ThermalPrinter = function (config) {
    return {
        ePosDev: null,
        printer: null,
        connected: false,
        ip: config.ip,
        port: config.port,
        deviceId: config.deviceId,
        enabled: config.enabled,
        institutionName: config.institutionName || 'PTSP',

        init() {
            if (!this.enabled || typeof epson === 'undefined') {
                console.warn('[ThermalPrinter] Printer tidak aktif atau SDK belum dimuat.');
                return;
            }
            this.connect();
        },

        connect() {
            this.ePosDev = new epson.ePOSDevice();
            this.ePosDev.connect(this.ip, this.port, (code) => {
                if (code === 'OK' || code === 'SSL_CONNECT_OK') {
                    this.ePosDev.createDevice(
                        this.deviceId,
                        this.ePosDev.DEVICE_TYPE_PRINTER,
                        { crypto: false, buffer: false },
                        (deviceObj, code) => {
                            if (code === 'OK') {
                                this.printer = deviceObj;
                                this.connected = true;
                                console.log('[ThermalPrinter] Terhubung ke printer.');
                            } else {
                                console.error('[ThermalPrinter] createDevice gagal:', code);
                            }
                        }
                    );
                } else {
                    console.error('[ThermalPrinter] Koneksi gagal:', code);
                }
            });
        },

        /**
         * Cetak tiket antrian ke thermal printer 80mm.
         * Format ESC/POS native — 42 karakter per baris.
         *
         * @param {Object} ticket - { ticketNumber, serviceName, visitorName, serviceDate, status }
         */
        printTicket(ticket) {
            if (!this.connected || !this.printer) {
                console.warn('[ThermalPrinter] Printer tidak terhubung. Cetak dibatalkan.');
                return false;
            }

            const prn = this.printer;
            const separator = '──────────────────────────────────────────';
            const now = new Date();
            const timestamp = now.toLocaleDateString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric'
            }) + ' ' + now.toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit'
            });

            prn.addTextLang('en');
            prn.addTextSmooth(true);

            // Header institusi
            prn.addTextAlign(prn.ALIGN_CENTER);
            prn.addTextSize(1, 1);
            prn.addTextStyle(false, false, true, prn.COLOR_1);
            prn.addText(this.institutionName + '\n');
            prn.addTextStyle(false, false, false, prn.COLOR_1);
            prn.addText('Sistem Pelayanan Terpadu Satu Pintu\n');
            prn.addText(separator + '\n');

            // Nomor tiket besar
            prn.addFeedLine(1);
            prn.addTextSize(3, 3);
            prn.addTextStyle(false, false, true, prn.COLOR_1);
            prn.addText(ticket.ticketNumber + '\n');
            prn.addFeedLine(1);

            // Detail tiket
            prn.addTextSize(1, 1);
            prn.addTextStyle(false, false, false, prn.COLOR_1);
            prn.addText(separator + '\n');
            prn.addTextAlign(prn.ALIGN_LEFT);
            prn.addText('Layanan : ' + ticket.serviceName + '\n');
            prn.addText('Nama    : ' + ticket.visitorName + '\n');
            prn.addText('Tanggal : ' + ticket.serviceDate + '\n');
            prn.addText('Status  : ' + ticket.status + '\n');

            // Barcode
            prn.addTextAlign(prn.ALIGN_CENTER);
            prn.addText(separator + '\n');
            prn.addFeedLine(1);
            prn.addBarcode(
                ticket.ticketNumber,
                prn.BARCODE_CODE128,
                prn.HRI_BELOW,
                prn.FONT_A,
                2,
                80
            );

            // Instruksi
            prn.addFeedLine(1);
            prn.addText(separator + '\n');
            prn.addText('Silakan tunggu panggilan nomor\n');
            prn.addText('antrian Anda di area tunggu.\n');
            prn.addFeedLine(1);
            prn.addTextSize(1, 1);
            prn.addText('Dicetak: ' + timestamp + '\n');

            // Cut
            prn.addFeedLine(3);
            prn.addCut(prn.CUT_FEED);

            // Kirim ke printer
            prn.send();

            return true;
        },

        disconnect() {
            if (this.ePosDev) {
                this.ePosDev.disconnect();
                this.connected = false;
                this.printer = null;
            }
        },
    };
};
