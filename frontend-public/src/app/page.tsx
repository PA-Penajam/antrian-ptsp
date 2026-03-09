import Link from 'next/link';

import { getInstitution, getServices } from '@/lib/api';

export const dynamic = 'force-dynamic';

export default async function HomePage() {
  const [institution, servicesRes] = await Promise.all([
    getInstitution().catch(() => null),
    getServices().catch(() => ({ data: [] })),
  ]);
  const services = servicesRes.data;

  const institutionName = institution?.name ?? 'PTSP';
  const institutionAddress = institution?.address ?? 'Alamat layanan belum tersedia.';
  const institutionPhone = institution?.phone ?? 'Kontak layanan belum tersedia.';
  const operatingHours = institution?.operating_hours ?? 'Jam operasional belum tersedia.';
  const bookingEnabledCount = services.filter((service) => service.booking_enabled).length;

  const institutionInfo = [
    { label: 'Jam Operasional', value: operatingHours },
    { label: 'Alamat', value: institutionAddress },
    { label: 'Kontak', value: institutionPhone },
  ];

  return (
    <div className="page-wrapper">
      <section className="banner-one">
        <div className="banner-one__shape-bg" />
        <div className="banner-one__shape-bg-2" />
        <div className="banner-one__shape-bg-3" />
        <div className="container">
          <div className="banner-one__title-box">
            <h5 className="banner-one__sub-title">{institutionName}</h5>
            <h1 className="banner-one__title">Sistem Antrian PTSP</h1>
          </div>

          <div className="row align-items-center gutter-y-30">
            <div className="col-xl-7 col-lg-7">
              <div className="banner-one__left sec-title-animation animation-style3">
                <p className="banner-one__text-1 title-animation">
                  {institutionName} menghadirkan layanan antrian publik yang lebih cepat, jelas,
                  dan tertib untuk membantu masyarakat memilih layanan, memahami persyaratan,
                  dan datang dengan persiapan yang tepat.
                </p>
                <p className="banner-one__text-2 title-animation">
                  Ambil antrian secara online, cek status tiket kapan saja, lalu pantau layanan
                  yang aktif langsung dari katalog PTSP.
                </p>

                <div className="banner-one__btn-and-satisfied-client-box">
                  <div className="banner-one__btn-box">
                    <Link href="/antrian" className="banner-one__btn thm-btn">
                      <span className="icon-right"></span> Ambil Antrian
                    </Link>
                  </div>
                  <div className="banner-one__btn-box">
                    <Link href="/antrian/cek" className="banner-one__btn thm-btn">
                      <span className="icon-search-1"></span> Cek Status Antrian
                    </Link>
                  </div>
                </div>
              </div>
            </div>

            <div className="col-xl-5 col-lg-5">
              <div className="about-one__right-content-box">
                <div className="section-title text-left">
                  <div className="section-title__tagline-box">
                    <div className="section-title__tagline-icon-box">
                      <div className="section-title__tagline-icon-1"></div>
                      <div className="section-title__tagline-icon-2"></div>
                    </div>
                    <span className="section-title__tagline">Ringkasan Layanan</span>
                  </div>
                  <h2 className="section-title__title">Akses Publik Lebih Tertib</h2>
                </div>

                <div className="about-one__points-box">
                  <ul className="list-unstyled about-one__points">
                    <li>
                      <div className="icon">
                        <span className="fas fa-check"></span>
                      </div>
                      <div className="text">
                        <p>{services.length} layanan aktif tersedia untuk pengunjung.</p>
                      </div>
                    </li>
                    <li>
                      <div className="icon">
                        <span className="fas fa-check"></span>
                      </div>
                      <div className="text">
                        <p>{bookingEnabledCount} layanan mendukung booking online.</p>
                      </div>
                    </li>
                  </ul>

                  <ul className="list-unstyled about-one__points">
                    <li>
                      <div className="icon">
                        <span className="fas fa-check"></span>
                      </div>
                      <div className="text">
                        <p>Pilih layanan lebih dulu agar persiapan berkas lebih jelas.</p>
                      </div>
                    </li>
                    <li>
                      <div className="icon">
                        <span className="fas fa-check"></span>
                      </div>
                      <div className="text">
                        <p>Status tiket tetap bisa dicek saat API layanan tersedia kembali.</p>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="layanan-tersedia" className="services-one">
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
              <span className="section-title__tagline">Layanan Tersedia</span>
            </div>
            <h2 className="section-title__title title-animation">
              Pilih katalog layanan PTSP yang sesuai dengan kebutuhan Anda
            </h2>
          </div>

          {services.length > 0 ? (
            <div className="row">
              {services.map((service) => (
                <div key={service.id} className="col-xl-4 col-lg-6 col-md-6">
                  <div className="services-one__single">
                    <div className="services-one__count"></div>
                    <div className="services-one__content-box">
                      <h3 className="services-one__title">{service.name}</h3>
                    </div>

                    <p>
                      {service.description ?? 'Deskripsi layanan belum tersedia. Silakan hubungi petugas PTSP untuk informasi lebih lanjut.'}
                    </p>

                    <div className="section-title__tagline-box mt-3">
                      <div className="section-title__tagline-icon-box">
                        <div className="section-title__tagline-icon-1"></div>
                        <div className="section-title__tagline-icon-2"></div>
                      </div>
                      <span className="section-title__tagline">
                        {service.booking_enabled ? 'Booking Online Aktif' : 'Booking Online Nonaktif'}
                      </span>
                    </div>

                    {(service.daily_quota !== null || service.remaining_quota !== null) && (
                      <p className="mt-3">
                        {service.daily_quota !== null && `Kuota harian: ${service.daily_quota}`}
                        {service.daily_quota !== null && service.remaining_quota !== null && ' - '}
                        {service.remaining_quota !== null && `Sisa kuota: ${service.remaining_quota}`}
                      </p>
                    )}

                    <div className="services-one__more-details mt-3">
                      <Link href={service.booking_enabled ? '/antrian' : '/antrian/cek'}>
                        <i className="icon-right"></i>{' '}
                        {service.booking_enabled ? 'Ambil Antrian' : 'Cek Status Antrian'}
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="row justify-content-center">
              <div className="col-xl-8 col-lg-10">
                <div className="about-one__right-content-box text-center">
                  <div className="section-title text-center">
                    <div className="section-title__tagline-box justify-content-center">
                      <div className="section-title__tagline-icon-box">
                        <div className="section-title__tagline-icon-1"></div>
                        <div className="section-title__tagline-icon-2"></div>
                      </div>
                      <span className="section-title__tagline">Layanan Belum Tersedia</span>
                    </div>
                    <h2 className="section-title__title">Katalog layanan akan tampil setelah data diaktifkan</h2>
                  </div>
                  <p>
                    Saat ini belum ada layanan publik yang dapat ditampilkan. Silakan cek kembali
                    beberapa saat lagi atau hubungi petugas PTSP untuk informasi terbaru.
                  </p>
                </div>
              </div>
            </div>
          )}
        </div>
      </section>

      <section className="about-one">
        <div className="about-one__shape-bg"></div>
        <div className="container">
          <div className="row gutter-y-30 align-items-center">
            <div className="col-xl-5 col-lg-5">
              <div className="about-one__right">
                <div className="section-title text-left sec-title-animation animation-style2">
                  <div className="section-title__tagline-box">
                    <div className="section-title__tagline-icon-box">
                      <div className="section-title__tagline-icon-1"></div>
                      <div className="section-title__tagline-icon-2"></div>
                    </div>
                    <span className="section-title__tagline">Informasi Pelayanan</span>
                  </div>
                  <h2 className="section-title__title title-animation">
                    Kunjungi PTSP dengan informasi yang sudah jelas
                  </h2>
                </div>

                <p className="about-one__text">
                  Gunakan informasi institusi berikut untuk menyiapkan kunjungan, memastikan jam
                  layanan, dan menghubungi petugas bila Anda membutuhkan konfirmasi lebih lanjut.
                </p>

                <div className="about-one__right-content-box">
                  <div className="about-one__points-box">
                    <ul className="list-unstyled about-one__points">
                      {institutionInfo.slice(0, 2).map((item) => (
                        <li key={item.label}>
                          <div className="icon">
                            <span className="fas fa-check"></span>
                          </div>
                          <div className="text">
                            <p>{`${item.label}: ${item.value}`}</p>
                          </div>
                        </li>
                      ))}
                    </ul>

                    <ul className="list-unstyled about-one__points">
                      {institutionInfo.slice(2).map((item) => (
                        <li key={item.label}>
                          <div className="icon">
                            <span className="fas fa-check"></span>
                          </div>
                          <div className="text">
                            <p>{`${item.label}: ${item.value}`}</p>
                          </div>
                        </li>
                      ))}
                      <li>
                        <div className="icon">
                          <span className="fas fa-check"></span>
                        </div>
                        <div className="text">
                          <p>Bawa identitas dan dokumen pendukung agar verifikasi lebih singkat.</p>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div className="col-xl-7 col-lg-7">
              <div className="process-one mt00">
                <div className="process-one__top">
                  <div className="process-one__top-title-box">
                    <div className="section-title text-left sec-title-animation animation-style2">
                      <div className="section-title__tagline-box">
                        <div className="section-title__tagline-icon-box">
                          <div className="section-title__tagline-icon-1"></div>
                          <div className="section-title__tagline-icon-2"></div>
                        </div>
                        <span className="section-title__tagline">Alur Pengunjung</span>
                      </div>
                      <h2 className="section-title__title title-animation">
                        Tiga langkah sederhana sebelum datang ke loket
                      </h2>
                    </div>
                  </div>
                </div>

                <div className="process-one__bottom">
                  <ul className="row list-unstyled">
                    <li className="col-xl-4 col-lg-4 col-md-4">
                      <div className="process-one__single">
                        <div className="process-one__title-box">
                          <h3 className="process-one__title">Pilih Layanan</h3>
                        </div>
                        <p className="process-one__text">
                          Tinjau katalog layanan dan pilih jenis pelayanan yang paling sesuai dengan kebutuhan Anda.
                        </p>
                        <div className="process-one__count"></div>
                      </div>
                    </li>
                    <li className="col-xl-4 col-lg-4 col-md-4">
                      <div className="process-one__single">
                        <div className="process-one__title-box">
                          <h3 className="process-one__title">Ambil Tiket</h3>
                        </div>
                        <p className="process-one__text">
                          Isi data pengunjung dengan benar untuk mendapatkan nomor antrian yang siap diproses.
                        </p>
                        <div className="process-one__count"></div>
                      </div>
                    </li>
                    <li className="col-xl-4 col-lg-4 col-md-4">
                      <div className="process-one__single">
                        <div className="process-one__title-box">
                          <h3 className="process-one__title">Pantau Status</h3>
                        </div>
                        <p className="process-one__text">
                          Cek status antrian dan datang sesuai jadwal layanan agar proses tetap tertib.
                        </p>
                        <div className="process-one__count"></div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
