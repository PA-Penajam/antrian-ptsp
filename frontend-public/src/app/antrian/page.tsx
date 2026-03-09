'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useCallback, useEffect, useMemo, useState } from 'react';

import { createBooking, getServices } from '@/lib/api';
import type { ApiError, BookingPayload, Service } from '@/types/api';

type WizardStep = 1 | 2 | 3;

type BookingFormData = {
  visitor_name: string;
  visitor_phone: string;
  visitor_identifier: string;
  service_date: string;
  notes: string;
};

type FieldErrors = Record<string, string[]>;

type FormSubmitEvent = {
  preventDefault(): void;
};

const stepItems: Array<{ step: WizardStep; title: string; description: string }> = [
  {
    step: 1,
    title: 'Pilih Layanan',
    description: 'Tentukan layanan PTSP yang ingin dibooking sebelum melanjutkan.',
  },
  {
    step: 2,
    title: 'Isi Data',
    description: 'Lengkapi data pengunjung dan pilih tanggal layanan yang tersedia.',
  },
  {
    step: 3,
    title: 'Konfirmasi',
    description: 'Periksa ringkasan booking lalu kirim ke sistem antrian online.',
  },
];

const initialFormData: BookingFormData = {
  visitor_name: '',
  visitor_phone: '',
  visitor_identifier: '',
  service_date: '',
  notes: '',
};

function formatDateForInput(date: Date): string {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function parseLocalDate(value: string): Date | null {
  const [year, month, day] = value.split('-').map(Number);

  if (!year || !month || !day) {
    return null;
  }

  return new Date(year, month - 1, day);
}

function isWeekendDate(value: string): boolean {
  const date = parseLocalDate(value);

  if (!date) {
    return false;
  }

  const day = date.getDay();

  return day === 0 || day === 6;
}

function formatDisplayDate(value: string): string {
  const date = parseLocalDate(value);

  if (!date) {
    return '-';
  }

  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date);
}

function canBookService(service: Service, selectedDate: string): boolean {
  if (!service.booking_enabled) {
    return false;
  }

  if (service.remaining_quota === null) {
    return true;
  }

  // remaining_quota hanya relevan untuk HARI INI
  // Jika tanggal yang dipilih bukan hari ini, jangan gunakan today's quota
  const today = new Date().toISOString().split('T')[0];
  if (selectedDate !== today) {
    return true;
  }

  return service.remaining_quota > 0;
}

export default function BookingWizardPage() {
  const router = useRouter();
  const [step, setStep] = useState<WizardStep>(1);
  const [selectedService, setSelectedService] = useState<Service | null>(null);
  const [formData, setFormData] = useState<BookingFormData>(initialFormData);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<FieldErrors>({});
  const [services, setServices] = useState<Service[]>([]);
  const [isLoadingServices, setIsLoadingServices] = useState(true);
  const [servicesError, setServicesError] = useState<string | null>(null);

  const bookingWindow = useMemo(() => {
    const today = new Date();
    const maxDate = new Date(today);
    maxDate.setDate(maxDate.getDate() + 14);

    return {
      min: formatDateForInput(today),
      max: formatDateForInput(maxDate),
    };
  }, []);

  const availableServices = useMemo(
    () => services.filter((service) => service.booking_enabled),
    [services],
  );

  const clearErrors = useCallback((keys: string[]) => {
    setErrors((currentErrors) => {
      const nextErrors = { ...currentErrors };

      keys.forEach((key) => {
        delete nextErrors[key];
      });

      return nextErrors;
    });
  }, []);

  const loadServices = useCallback(async () => {
    setIsLoadingServices(true);
    setServicesError(null);

    try {
      const response = await getServices();
      setServices(response.data);
    } catch {
      setServices([]);
      setServicesError('Layanan booking belum dapat dimuat. Silakan coba lagi beberapa saat lagi.');
    } finally {
      setIsLoadingServices(false);
    }
  }, []);

  useEffect(() => {
    void loadServices();
  }, [loadServices]);

  useEffect(() => {
    if (!selectedService) {
      return;
    }

    const latestSelectedService = availableServices.find((service) => service.id === selectedService.id);

    if (!latestSelectedService) {
      setSelectedService(null);
      setStep(1);
      setErrors((currentErrors) => ({
        ...currentErrors,
        service_id: ['Layanan yang dipilih sudah tidak tersedia. Silakan pilih ulang.'],
      }));

      return;
    }

    setSelectedService(latestSelectedService);
  }, [availableServices, selectedService]);

  function validateBookingDetails(): FieldErrors {
    const nextErrors: FieldErrors = {};

    if (!selectedService) {
      nextErrors.service_id = ['Pilih layanan terlebih dahulu.'];
    }

    if (!formData.visitor_name.trim()) {
      nextErrors.visitor_name = ['Nama pengunjung wajib diisi.'];
    }

    if (!formData.service_date) {
      nextErrors.service_date = ['Tanggal layanan wajib diisi.'];
    } else if (formData.service_date < bookingWindow.min || formData.service_date > bookingWindow.max) {
      nextErrors.service_date = ['Tanggal layanan harus berada dalam rentang hari ini sampai 14 hari ke depan.'];
    } else if (isWeekendDate(formData.service_date)) {
      nextErrors.service_date = ['Booking online hanya tersedia untuk hari kerja (Senin-Jumat).'];
    }

    return nextErrors;
  }

  function selectService(service: Service): void {
    if (!canBookService(service, formData.service_date)) {
      setErrors((currentErrors) => ({
        ...currentErrors,
        service_id: [formData.service_date === new Date().toISOString().split('T')[0] 
          ? 'Layanan ini sedang tidak dapat dibooking karena kuota hari ini sudah penuh.' 
          : 'Layanan ini sedang tidak dapat dibooking.'],
      }));

      return;
    }

    setSelectedService(service);
    clearErrors(['service_id', '_form']);
  }

  function handleInputChange<K extends keyof BookingFormData>(
    field: K,
    value: BookingFormData[K],
  ): void {
    setFormData((currentData) => ({
      ...currentData,
      [field]: value,
    }));

    clearErrors([field, '_form']);

    if (field === 'service_date' && typeof value === 'string' && isWeekendDate(value)) {
      setErrors((currentErrors) => ({
        ...currentErrors,
        service_date: ['Booking online hanya tersedia untuk hari kerja (Senin-Jumat).'],
      }));
    }
  }

  function goToStep(nextStep: WizardStep): void {
    if (nextStep === 1) {
      setStep(1);
      return;
    }

    if (nextStep === 2) {
      if (!selectedService) {
        setErrors((currentErrors) => ({
          ...currentErrors,
          service_id: ['Pilih layanan terlebih dahulu sebelum melanjutkan.'],
        }));

        return;
      }

      setStep(2);
      return;
    }

    const validationErrors = validateBookingDetails();

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      setStep(selectedService ? 2 : 1);
      return;
    }

    setErrors((currentErrors) => {
      const nextErrors = { ...currentErrors };
      delete nextErrors.visitor_name;
      delete nextErrors.service_date;
      delete nextErrors._form;
      return nextErrors;
    });
    setStep(3);
  }

  async function handleSubmit(event: FormSubmitEvent): Promise<void> {
    event.preventDefault();

    const validationErrors = validateBookingDetails();

    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      setStep(selectedService ? 2 : 1);
      return;
    }

    if (!selectedService) {
      return;
    }

    const payload: BookingPayload = {
      service_id: selectedService.id,
      service_date: formData.service_date,
      visitor_name: formData.visitor_name.trim(),
      visitor_identifier: formData.visitor_identifier.trim() || undefined,
      visitor_phone: formData.visitor_phone.trim() || undefined,
      notes: formData.notes.trim() || undefined,
    };

    setIsSubmitting(true);
    clearErrors(['_form']);

    try {
      const ticket = await createBooking(payload);
      router.push(`/antrian/konfirmasi/${ticket.data.ticket_number}`);
    } catch (error) {
      const apiError = error as ApiError;

      if (apiError.errors) {
        setErrors(apiError.errors);
      } else {
        setErrors({ _form: [apiError.message || 'Booking gagal diproses. Silakan coba lagi.'] });
      }

      setStep(2);
    } finally {
      setIsSubmitting(false);
    }
  }

  function renderFieldError(field: string) {
    const messages = errors[field];

    if (!messages?.length) {
      return null;
    }

    return <div className="text-danger mt-2">{messages[0]}</div>;
  }

  return (
    <div className="page-wrapper">
      <section className="page-header">
        <div className="container">
          <div className="page-header__inner">
            <h2>
              Booking <span>Antrian</span>
            </h2>
            <div className="thm-breadcrumb__inner">
              <ul className="thm-breadcrumb list-unstyled">
                <li>
                  <i className="icon-home"></i>
                  <Link href="/">Beranda</Link>
                </li>
                <li>
                  <span></span>
                </li>
                <li>Booking Antrian</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <section className="services-one pdb90">
        <div className="services-one__shape-1"></div>
        <div className="services-one__shape-2"></div>
        <div className="services-one__shape-3"></div>
        <div className="container">
          <div className="section-title text-center sec-title-animation animation-style1">
            <div className="section-title__tagline-box justify-content-center">
              <div className="section-title__tagline-icon-box">
                <div className="section-title__tagline-icon-1"></div>
                <div className="section-title__tagline-icon-2"></div>
              </div>
              <span className="section-title__tagline">Booking Online PTSP</span>
            </div>
            <h2 className="section-title__title">Selesaikan booking antrian publik dalam tiga langkah</h2>
          </div>

          <div className="row gutter-y-30 mb-4">
            {stepItems.map((item) => {
              const statusLabel = step === item.step ? 'Langkah Aktif' : step > item.step ? 'Selesai' : 'Menunggu';

              return (
                <div key={item.step} className="col-xl-4 col-lg-4 col-md-6">
                  <div className="process-one__single">
                    <div className="process-one__title-box">
                      <h3 className="process-one__title">Step 0{item.step}</h3>
                    </div>
                    <p className="process-one__text mb-2">{item.title}</p>
                    <p>{item.description}</p>
                    <div className="section-title__tagline-box mt-3">
                      <div className="section-title__tagline-icon-box">
                        <div className="section-title__tagline-icon-1"></div>
                        <div className="section-title__tagline-icon-2"></div>
                      </div>
                      <span className="section-title__tagline">{statusLabel}</span>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          <form onSubmit={handleSubmit} className="row gutter-y-30 align-items-start">
            <div className="col-xl-8 col-lg-7">
              {errors._form?.length ? (
                <div className="alert alert-danger mb-4" role="alert">
                  {errors._form[0]}
                </div>
              ) : null}

              {step === 1 ? (
                <div className="contact-page__right">
                  <div className="contact-page__contact-form-title-box mb-4">
                    <h3 className="contact-page__contact-form-title">Step 1 - Pilih layanan yang ingin dibooking</h3>
                  </div>

                  {renderFieldError('service_id')}

                  {isLoadingServices ? (
                    <div className="about-one__right-content-box mt-4">
                      <p>Memuat daftar layanan yang mendukung booking online...</p>
                    </div>
                  ) : null}

                  {servicesError ? (
                    <div className="about-one__right-content-box mt-4">
                      <p>{servicesError}</p>
                      <div className="contact-page__btn-box mt-4">
                        <button type="button" className="thm-btn contact-page__btn" onClick={() => void loadServices()}>
                          <span className="icon-right"></span> Coba Muat Ulang
                        </button>
                      </div>
                    </div>
                  ) : null}

                  {!isLoadingServices && !servicesError ? (
                    availableServices.length > 0 ? (
                      <div className="row">
                        {availableServices.map((service) => {
                          const serviceIsSelected = selectedService?.id === service.id;
                          const serviceIsBookable = canBookService(service, formData.service_date);

                          return (
                            <div key={service.id} className="col-xl-6 col-md-6">
                              <div className="services-one__single">
                                <div className="services-one__count"></div>
                                <div className="services-one__content-box">
                                  <h3 className="services-one__title">{service.name}</h3>
                                </div>

                                <p>
                                  {service.description ?? 'Deskripsi layanan belum tersedia. Silakan konfirmasi detail layanan ke petugas PTSP.'}
                                </p>

                                <ul className="list-unstyled about-one__points mt-4">
                                  <li>
                                    <div className="icon">
                                      <span className="fas fa-check"></span>
                                    </div>
                                    <div className="text">
                                      <p>
                                        {service.remaining_quota === null
                                          ? 'Kuota mengikuti kapasitas layanan.'
                                          : `Sisa kuota: ${service.remaining_quota}`}
                                      </p>
                                    </div>
                                  </li>
                                  <li>
                                    <div className="icon">
                                      <span className="fas fa-check"></span>
                                    </div>
                                    <div className="text">
                                      <p>
                                        {service.requirements
                                          ? `Persyaratan: ${service.requirements}`
                                          : 'Persyaratan akan diinformasikan saat verifikasi.'}
                                      </p>
                                    </div>
                                  </li>
                                </ul>

                                <div className="contact-page__btn-box mt-4">
                                  <button
                                    type="button"
                                    className="thm-btn contact-page__btn"
                                    disabled={!serviceIsBookable}
                                    onClick={() => selectService(service)}
                                  >
                                    <span className={serviceIsSelected ? 'fas fa-check' : 'icon-right'}></span>{' '}
                                    {serviceIsSelected ? 'Dipilih' : serviceIsBookable ? 'Pilih' : 'Kuota Penuh'}
                                  </button>
                                </div>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    ) : (
                      <div className="about-one__right-content-box mt-4">
                        <p>Belum ada layanan publik dengan booking online yang aktif saat ini.</p>
                      </div>
                    )
                  ) : null}

                  <div className="contact-page__btn-box mt-4">
                    <button
                      type="button"
                      className="thm-btn contact-page__btn"
                      disabled={!selectedService}
                      onClick={() => goToStep(2)}
                    >
                      <span className="icon-right"></span> Lanjut
                    </button>
                  </div>
                </div>
              ) : null}

              {step === 2 ? (
                <div className="contact-page__right">
                  <div className="contact-page__contact-form-title-box mb-4">
                    <h3 className="contact-page__contact-form-title">Step 2 - Isi data pengunjung</h3>
                    <p className="contact-page__contact-form-text">
                      Booking online hanya tersedia untuk hari kerja dalam rentang hari ini sampai 14 hari ke depan.
                    </p>
                  </div>

                  <div className="about-one__right-content-box mb-4">
                    <ul className="list-unstyled about-one__points">
                      <li>
                        <div className="icon">
                          <span className="fas fa-check"></span>
                        </div>
                        <div className="text">
                          <p>Layanan terpilih: {selectedService?.name ?? 'Belum dipilih'}</p>
                        </div>
                      </li>
                      <li>
                        <div className="icon">
                          <span className="fas fa-check"></span>
                        </div>
                        <div className="text">
                          <p>
                            {selectedService?.requirements
                              ? `Persyaratan: ${selectedService.requirements}`
                              : 'Persyaratan akan diinformasikan oleh petugas PTSP.'}
                          </p>
                        </div>
                      </li>
                    </ul>

                    {renderFieldError('service_id')}
                  </div>

                  <div className="contact-page__form">
                    <div className="row">
                      <div className="col-xl-6 col-md-6">
                        <div className="form-group mb-4">
                          <label className="form-label" htmlFor="visitor_name">
                            Nama Pengunjung *
                          </label>
                          <input
                            id="visitor_name"
                            type="text"
                            className="form-control"
                            value={formData.visitor_name}
                            onChange={(event) => handleInputChange('visitor_name', event.target.value)}
                          />
                          {renderFieldError('visitor_name')}
                        </div>
                      </div>

                      <div className="col-xl-6 col-md-6">
                        <div className="form-group mb-4">
                          <label className="form-label" htmlFor="service_date">
                            Tanggal Layanan *
                          </label>
                          <input
                            id="service_date"
                            type="date"
                            className="form-control"
                            min={bookingWindow.min}
                            max={bookingWindow.max}
                            value={formData.service_date}
                            onChange={(event) => handleInputChange('service_date', event.target.value)}
                          />
                          <div className="text-muted mt-2">Pilih tanggal Senin-Jumat. Sabtu dan Minggu tidak tersedia untuk booking online.</div>
                          {renderFieldError('service_date')}
                        </div>
                      </div>

                      <div className="col-xl-6 col-md-6">
                        <div className="form-group mb-4">
                          <label className="form-label" htmlFor="visitor_phone">
                            Nomor Telepon
                          </label>
                          <input
                            id="visitor_phone"
                            type="text"
                            className="form-control"
                            value={formData.visitor_phone}
                            onChange={(event) => handleInputChange('visitor_phone', event.target.value)}
                          />
                          {renderFieldError('visitor_phone')}
                        </div>
                      </div>

                      <div className="col-xl-6 col-md-6">
                        <div className="form-group mb-4">
                          <label className="form-label" htmlFor="visitor_identifier">
                            Nomor Identitas
                          </label>
                          <input
                            id="visitor_identifier"
                            type="text"
                            className="form-control"
                            value={formData.visitor_identifier}
                            onChange={(event) => handleInputChange('visitor_identifier', event.target.value)}
                          />
                          {renderFieldError('visitor_identifier')}
                        </div>
                      </div>

                      <div className="col-xl-12">
                        <div className="form-group mb-4">
                          <label className="form-label" htmlFor="notes">
                            Catatan Tambahan
                          </label>
                          <textarea
                            id="notes"
                            className="form-control"
                            rows={5}
                            value={formData.notes}
                            onChange={(event) => handleInputChange('notes', event.target.value)}
                          />
                          {renderFieldError('notes')}
                        </div>
                      </div>
                    </div>

                    <div className="d-flex flex-wrap gap-3 mt-2">
                      <button type="button" className="thm-btn contact-page__btn" onClick={() => goToStep(1)}>
                        <span className="fas fa-arrow-left"></span> Kembali
                      </button>
                      <button type="button" className="thm-btn contact-page__btn" onClick={() => goToStep(3)}>
                        <span className="icon-right"></span> Lanjut
                      </button>
                    </div>
                  </div>
                </div>
              ) : null}

              {step === 3 ? (
                <div className="contact-page__right">
                  <div className="contact-page__contact-form-title-box mb-4">
                    <h3 className="contact-page__contact-form-title">Step 3 - Konfirmasi booking</h3>
                    <p className="contact-page__contact-form-text">
                      Periksa ringkasan berikut sebelum mengirim permintaan booking ke sistem antrian PTSP.
                    </p>
                  </div>

                  <div className="row gutter-y-30">
                    <div className="col-xl-6 col-md-6">
                      <div className="about-one__right-content-box h-100">
                        <ul className="list-unstyled about-one__points">
                          <li>
                            <div className="icon">
                              <span className="fas fa-check"></span>
                            </div>
                            <div className="text">
                              <p>Layanan: {selectedService?.name ?? '-'}</p>
                            </div>
                          </li>
                          <li>
                            <div className="icon">
                              <span className="fas fa-check"></span>
                            </div>
                            <div className="text">
                              <p>Tanggal layanan: {formatDisplayDate(formData.service_date)}</p>
                            </div>
                          </li>
                          <li>
                            <div className="icon">
                              <span className="fas fa-check"></span>
                            </div>
                            <div className="text">
                              <p>Nama pengunjung: {formData.visitor_name || '-'}</p>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>

                    <div className="col-xl-6 col-md-6">
                      <div className="about-one__right-content-box h-100">
                        <ul className="list-unstyled about-one__points">
                          <li>
                            <div className="icon">
                              <span className="fas fa-check"></span>
                            </div>
                            <div className="text">
                              <p>Nomor telepon: {formData.visitor_phone || '-'}</p>
                            </div>
                          </li>
                          <li>
                            <div className="icon">
                              <span className="fas fa-check"></span>
                            </div>
                            <div className="text">
                              <p>Nomor identitas: {formData.visitor_identifier || '-'}</p>
                            </div>
                          </li>
                          <li>
                            <div className="icon">
                              <span className="fas fa-check"></span>
                            </div>
                            <div className="text">
                              <p>Catatan: {formData.notes || '-'}</p>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div className="d-flex flex-wrap gap-3 mt-4">
                    <button type="button" className="thm-btn contact-page__btn" onClick={() => goToStep(2)}>
                      <span className="fas fa-arrow-left"></span> Kembali
                    </button>
                    <button type="submit" className="thm-btn contact-page__btn" disabled={isSubmitting}>
                      <span className={isSubmitting ? 'fas fa-spinner fa-spin' : 'icon-right'}></span>{' '}
                      {isSubmitting ? 'Mengirim...' : 'Konfirmasi & Kirim'}
                    </button>
                  </div>
                </div>
              ) : null}
            </div>

            <div className="col-xl-4 col-lg-5">
              <div className="about-one__right-content-box mb-4">
                <div className="section-title text-left mb-3">
                  <div className="section-title__tagline-box">
                    <div className="section-title__tagline-icon-box">
                      <div className="section-title__tagline-icon-1"></div>
                      <div className="section-title__tagline-icon-2"></div>
                    </div>
                    <span className="section-title__tagline">Ringkasan Aktif</span>
                  </div>
                  <h3 className="section-title__title">Pilihan booking Anda</h3>
                </div>

                <ul className="list-unstyled about-one__points">
                  <li>
                    <div className="icon">
                      <span className="fas fa-check"></span>
                    </div>
                    <div className="text">
                      <p>Layanan: {selectedService?.name ?? 'Belum dipilih'}</p>
                    </div>
                  </li>
                  <li>
                    <div className="icon">
                      <span className="fas fa-check"></span>
                    </div>
                    <div className="text">
                      <p>
                        Tanggal: {formData.service_date ? formatDisplayDate(formData.service_date) : 'Belum dipilih'}
                      </p>
                    </div>
                  </li>
                  <li>
                    <div className="icon">
                      <span className="fas fa-check"></span>
                    </div>
                    <div className="text">
                      <p>Pengunjung: {formData.visitor_name || 'Belum diisi'}</p>
                    </div>
                  </li>
                </ul>
              </div>

              <div className="about-one__right-content-box">
                <div className="section-title text-left mb-3">
                  <div className="section-title__tagline-box">
                    <div className="section-title__tagline-icon-box">
                      <div className="section-title__tagline-icon-1"></div>
                      <div className="section-title__tagline-icon-2"></div>
                    </div>
                    <span className="section-title__tagline">Petunjuk</span>
                  </div>
                  <h3 className="section-title__title">Hal yang perlu diperhatikan</h3>
                </div>

                <ul className="list-unstyled about-one__points">
                  <li>
                    <div className="icon">
                      <span className="fas fa-check"></span>
                    </div>
                    <div className="text">
                      <p>Pilih layanan yang sesuai agar persyaratan berkas dapat disiapkan sejak awal.</p>
                    </div>
                  </li>
                  <li>
                    <div className="icon">
                      <span className="fas fa-check"></span>
                    </div>
                    <div className="text">
                      <p>Booking online hanya untuk hari kerja dan maksimal 14 hari ke depan.</p>
                    </div>
                  </li>
                  <li>
                    <div className="icon">
                      <span className="fas fa-check"></span>
                    </div>
                    <div className="text">
                      <p>Setelah booking berhasil, Anda akan diarahkan ke halaman konfirmasi tiket.</p>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </form>
        </div>
      </section>
    </div>
  );
}
