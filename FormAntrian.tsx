import React, { useState, useEffect, useMemo, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import { X } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";

import { Gerai, Motor, MotorType, PaketLayanan } from "@/types";
import {
  getMotorTypes,
  getMotors,
  getPackagesForMotor,
  createCustomerProfile,
  createCustomerMotor,
  createServiceQueue,
  searchCustomerByPhoneNumber,
  searchCustomerByPlat,
} from "@/utils/ggAPI";
import { FoundCustomer, SavedCustomer, SavedMotor } from "@/types/formAntrian";
import { getGerais } from "@/utils/ggsAPI";

export default function FormAntrian() {
  const navigate = useNavigate();

  const [step, setStep] = useState(1);
  const [isCustomerFound, setIsCustomerFound] = useState(false);
  const [motors, setMotors] = useState<Motor[]>([]);
  const [motorTypes, setMotorTypes] = useState<MotorType[]>([]);
  const [availablePackages, setAvailablePackages] = useState<PaketLayanan[]>(
    []
  );
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [savedCustomer, setSavedCustomer] = useState<SavedCustomer | null>(
    null
  );
  const [savedMotor, setSavedMotor] = useState<SavedMotor | null>(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [foundCustomerMotors, setFoundCustomerMotors] = useState<SavedMotor[]>(
    []
  );
  const [nama, setNama] = useState("");
  const [noWA, setNoWA] = useState(""); // State baru untuk noWA
  const [plat, setPlat] = useState("");
  const [selectedMotor, setSelectedMotor] = useState<Motor | null>(null);
  const [sumberInfo, setSumberInfo] = useState("");
  const [sudahChat, setSudahChat] = useState("");
  const [selectedType, setSelectedType] = useState<string | null>(null);
  const [selectedSuspensi, setSelectedSuspensi] = useState<PaketLayanan[]>([]);
  const [selectedLayananKomstir, setSelectedLayananKomstir] =
    useState<PaketLayanan | null>(null);
  const [totalHarga, setTotalHarga] = useState<number>(0);
  const [searchQuerySuspensi, setSearchQuerySuspensi] = useState("");
  const [searchQueryKomstir, setSearchQueryKomstir] = useState("");
  const [searchQueryMotor, setSearchQueryMotor] = useState("");
  const [activeDropdownSuspensi, setActiveDropdownSuspensi] = useState(false);
  const [activeDropdownKomstir, setActiveDropdownKomstir] = useState(false);
  const [activeDropdownMotor, setActiveDropdownMotor] = useState(false);
  const [gerais, setGerais] = useState<Gerai[]>([]);
  const [selectedGerai, setSelectedGerai] = useState<string>("");
  const [shockOnly, setShockOnly] = useState<boolean | undefined>(undefined);
  const [isCheckedGaransi, setIsCheckedGaransi] = useState({
    suspensi: false,
    komstir: false,
  });

  useEffect(() => {
    setIsLoading(true);
    Promise.all([getMotorTypes({}), getMotors({})])
      .then(([motorTypesData, motorsData]) => {
        setMotorTypes(motorTypesData);
        setMotors(motorsData);
      })
      .catch(console.error)
      .finally(() => setIsLoading(false));
  }, []);

  useEffect(() => {
    if (step !== 2 || !selectedMotor) {
      setAvailablePackages([]);
      return;
    }
    setIsLoading(true);
    getPackagesForMotor(selectedMotor.id)
      .then((data) => setAvailablePackages(data || []))
      .catch(console.error)
      .finally(() => setIsLoading(false));
  }, [selectedMotor, step]);

  const { layananSuspensi, layananKomstir } = useMemo<{
    layananSuspensi: PaketLayanan[];
    layananKomstir: PaketLayanan[];
  }>(() => {
    const suspensi: PaketLayanan[] = [];
    const komstir: PaketLayanan[] = [];
    availablePackages.forEach((pkg) => {
      if (pkg.type_service === "SUSPENSI") suspensi.push(pkg);
      if (pkg.type_service === "KOMSTIR") komstir.push(pkg);
    });
    return { layananSuspensi: suspensi, layananKomstir: komstir };
  }, [availablePackages]);

  const filteredLayananSuspensi = useMemo(() => {
    if (!searchQuerySuspensi) {
      return layananSuspensi;
    }
    return layananSuspensi.filter((item) =>
      item.name.toLowerCase().includes(searchQuerySuspensi.toLowerCase())
    );
  }, [searchQuerySuspensi, layananSuspensi]);

  const filteredLayananKomstir = useMemo(() => {
    if (!searchQueryKomstir) {
      return layananKomstir;
    }
    return layananKomstir.filter((item) =>
      item.name.toLowerCase().includes(searchQueryKomstir.toLowerCase())
    );
  }, [searchQueryKomstir, layananKomstir]);

  const filteredMotors = useMemo(() => {
    if (!searchQueryMotor) {
      return motors;
    }
    return motors.filter((motor) =>
      motor.name.toLowerCase().includes(searchQueryMotor.toLowerCase())
    );
  }, [searchQueryMotor, motors]);

  useEffect(() => {
    const totalSuspensi = selectedSuspensi.reduce(
      (acc, item) => acc + item.total_price,
      0
    );
    const totalKomstir = selectedLayananKomstir?.total_price || 0;

    let total = 0;
    if (selectedType === "suspensi") {
      total = totalSuspensi;
    } else if (selectedType === "komstir") {
      total = totalKomstir;
    } else if (selectedType === "komstir_suspensi") {
      total = totalSuspensi + totalKomstir;
    }
    setTotalHarga(total);
  }, [selectedType, selectedLayananKomstir, selectedSuspensi]);

  useEffect(() => {
    setSelectedSuspensi([]);
    setSelectedLayananKomstir(null);
    getGerais().then((data) => {
      {
        setGerais(data);
      }
    });
  }, [selectedType]);

  const motorTypeName = useMemo(() => {
    if (!selectedMotor || motorTypes.length === 0) return "";
    const motorType = motorTypes.find(
      (type) => type.id === selectedMotor.motor_type_id
    );
    return motorType ? motorType.name : "";
  }, [selectedMotor, motorTypes]);

  const isKlaimGaransi = useMemo(() => {
    return isCheckedGaransi.suspensi || isCheckedGaransi.komstir ? true : false;
  }, [isCheckedGaransi]);

  const handleSearchCustomer = async () => {
    if (!searchQuery) {
      alert("Harap masukkan Nomor WhatsApp atau Plat Motor.");
      return;
    }

    setIsLoading(true);

    let customerResponse: FoundCustomer | null = null;
    let motorResponse: SavedMotor | null = null;

    try {
      const isPlat = !/^\d+$/.test(searchQuery.replace(/\s/g, ""));

      if (isPlat) {
        const response = await searchCustomerByPlat(searchQuery);
        customerResponse = response.customer;
        motorResponse = response.motor;
      } else {
        customerResponse = await searchCustomerByPhoneNumber(searchQuery);
        if (
          customerResponse &&
          customerResponse.motors &&
          customerResponse.motors.length > 0
        ) {
          motorResponse = customerResponse.motors[0];
        }
      }

      if (customerResponse) {
        setNama(customerResponse.nama);
        setNoWA(customerResponse.noWA.replace("62", "")); // Mengisi input noWA
        setSumberInfo(customerResponse.sumber_info || "");
        setSudahChat(customerResponse.sudah_chat || "");
        setSavedCustomer(customerResponse);
        setFoundCustomerMotors(customerResponse.motors || []);
        setIsCustomerFound(true);
        alert(`Pelanggan ditemukan: ${customerResponse.nama}`);

        if (motorResponse) {
          const mr = motorResponse;
          setPlat(mr.plat_motor);
          const motorFromMasterList = motors.find(
            (m) => m.name === mr.nama_motor
          );
          setSelectedMotor(motorFromMasterList || null);
          setSavedMotor(mr);
          setSearchQueryMotor(mr.nama_motor); // Set search query for motor
        } else {
          setPlat("");
          setSelectedMotor(null);
          setSavedMotor(null);
          setSearchQueryMotor("");
        }
      } else {
        alert(
          "Pelanggan tidak ditemukan. Silakan lengkapi data untuk mendaftar."
        );
        setIsCustomerFound(false);
        setNama("");
        setNoWA(""); // Mereset noWA
        setPlat("");
        setSelectedMotor(null);
        setSavedCustomer(null);
        setFoundCustomerMotors([]);
        setSearchQueryMotor("");
      }
    } catch (error) {
      console.error("Gagal mencari customer:", error);
      alert(
        "Pelanggan tidak ditemukan. Silakan lengkapi data untuk mendaftar."
      );
      setIsCustomerFound(false);
      setNama("");
      setNoWA(""); // Mereset noWA
      setPlat("");
      setSelectedMotor(null);
      setSavedCustomer(null);
      setFoundCustomerMotors([]);
      setSearchQueryMotor("");
    } finally {
      setIsLoading(false);
    }
  };

  const handleSaveCustomerAndMotor = async () => {
    if (!nama || !noWA) {
      alert("Harap lengkapi Nama, Plat, dan pilihan Motor.");
      return;
    }

    setIsSubmitting(true);
    try {
      if (isCustomerFound) {
        const isMotorAlreadyRegistered = foundCustomerMotors.some(
          (m) => m.plat_motor === plat
        );

        if (!isMotorAlreadyRegistered) {
          if (!savedCustomer) {
            alert("Data pelanggan tidak ditemukan.");
            setIsSubmitting(false);
            return;
          }
          const motorToSave = await createCustomerMotor({
            customer_profile_id: savedCustomer.id,
            nama_motor: selectedMotor?.name ?? "",
            jenis_motor: motorTypeName,
            plat_motor: plat.toUpperCase(),
          });
          setSavedMotor(motorToSave);
        }
      } else {
        if (!sumberInfo || !sudahChat) {
          alert(
            "Harap lengkapi Sumber Info dan Status Chat untuk pelanggan baru."
          );
          setIsSubmitting(false);
          return;
        }

        const customerSearchedByPlat = await searchCustomerByPlat(plat);
        if (customerSearchedByPlat.motor)
          throw new Error(
            "Plat motor sudah terdaftar pada pelanggan lain. Silakan cek kembali."
          );
        const customerToSave = await createCustomerProfile({
          nama,
          noWA: `62${noWA}`, // Menggunakan state noWA
          sumber_info: sumberInfo,
          sudah_chat: sudahChat,
        });
        setSavedCustomer(customerToSave);

        if (customerToSave && selectedMotor) {
          const motorResponse = await createCustomerMotor({
            customer_profile_id: customerToSave.id,
            nama_motor: selectedMotor.name,
            jenis_motor: motorTypeName,
            plat_motor: plat.toUpperCase(),
          });
          if (motorResponse) {
            setSavedMotor(motorResponse);
          } else {
            setSavedMotor(null);
          }
        }
      }
      setStep(2);
    } catch (error) {
      console.error("Gagal menyimpan data:", error);
      alert("Gagal menyimpan data. Pastikan No. WA atau Plat tidak duplikat.");
    } finally {
      setIsSubmitting(false);
    }
  };
  function transformServiceData(input: any): any {
    const output: any = {};

    for (const key in input) {
      const serviceData = input[key];

      // Skip jika data null atau array kosong
      if (
        !serviceData ||
        (Array.isArray(serviceData) && serviceData.length === 0)
      ) {
        continue;
      }

      // Extract service type dari key (misal: selectedLayananKomstir -> komstir)
      const serviceType = key
        .toLowerCase()
        .replace("selected", "")
        .replace("layanan", "")
        .trim();

      // Handle jika data adalah array atau single object
      const services = Array.isArray(serviceData) ? serviceData : [serviceData];

      // Transform setiap service menjadi layanan_details
      const layananDetails: any = services.map((service) => ({
        price: service.total_price,
        layanan: service.name,
        cc_range: service.cc_range === "ALL" ? null : service.cc_range,
        warranty: service.warranty,
        part_motor: service.motor_parts || [],
        part_included: service.part_included || [],
        used_spareparts: [],
      }));

      // Buat output structure
      output[serviceType] = {
        status: "MENUNGGU ANTRIAN",
        layanan_details: layananDetails,
        klaim_garansi_expire: null,
      };
    }

    return output;
  }

  const handleSubmitQueue = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const inputData = {
      selectedLayananKomstir,
      selectedSuspensi,
    };
    const services = transformServiceData(inputData);
    if (
      !savedCustomer ||
      !savedMotor ||
      !selectedMotor ||
      (selectedSuspensi.length === 0 && !selectedLayananKomstir)
    ) {
      alert("Harap lengkapi semua data");
      return;
    }
    setIsSubmitting(true);

    const allServiceIds = [
      ...selectedSuspensi.map((s) => s.id),
      ...(selectedLayananKomstir ? [selectedLayananKomstir.id] : []),
    ];

    if (savedCustomer && savedMotor && shockOnly !== undefined) {
      const payload = {
        customer_profile_id: savedCustomer.id,
        customer_motor_id: savedMotor.id,
        selected_package_ids: allServiceIds,
        gerai_id: parseInt(selectedGerai),
        shock_only: shockOnly,
        klaim_garansi: isKlaimGaransi,
        services,
      };
      try {
        await createServiceQueue(payload);
        navigate("/admin/antrian");
      } catch (error) {
        console.error("Gagal memasukkan ke antrian:", error);
        alert("Gagal memasukkan ke antrian. Cek konsol untuk detail.");
      } finally {
        setIsSubmitting(false);
      }
    } else {
      setIsSubmitting(false);
      alert("Gagal mengirim data. Data pelanggan atau motor tidak valid.");
    }
  };

  const handleSelectGerai = (value: string) => {
    setSelectedGerai(value);
  };

  const inputClass =
    "bg-neutral-800 border-neutral-700 text-white focus:ring-orange-500 focus:border-orange-500";
  const triggerClass =
    "bg-neutral-800 border-neutral-700 hover:bg-neutral-700/50 focus:ring-orange-500 text-white";
  const contentClass = "bg-neutral-900 border-neutral-800 text-neutral-200";

  const handleClaimGaransiCheckKomstir = useCallback(() => {
    setIsCheckedGaransi((prev) => ({
      ...prev,
      komstir: !prev.komstir,
    }));
    if (!isCheckedGaransi.komstir) {
      if (selectedLayananKomstir) {
        setSelectedLayananKomstir({
          ...selectedLayananKomstir,
          total_price: 0,
        });
      }
    } else {
      const selectedKomstir = layananKomstir.find(
        (layanan) => layanan.id === selectedLayananKomstir?.id
      );
      if (selectedKomstir) {
        setSelectedLayananKomstir(selectedKomstir);
      }
    }
  }, [isCheckedGaransi, selectedLayananKomstir, layananKomstir]);

  const handleClaimGaransiCheckSuspensi = useCallback(() => {
    setIsCheckedGaransi((prev) => ({
      ...prev,
      suspensi: !prev.suspensi,
    }));
    if (!isCheckedGaransi.suspensi) {
      const suspensiKlaimGaransi = selectedSuspensi.map((s) => {
        s.before_price = s.total_price;
        s.total_price = 0;
        return s;
      });
      setSelectedSuspensi([...suspensiKlaimGaransi]);
    } else {
      let arr: any = [];
      [...selectedSuspensi].forEach((layanan) => {
        layanan.total_price = layanan.before_price;
        arr.push(layanan);
      });
      setSelectedSuspensi(arr);
    }
  }, [isCheckedGaransi, selectedSuspensi]);

  return (
    <div className="min-h-screen bg-black text-neutral-200 p-4 sm:p-8">
      <div className="flex flex-row justify-center items-center mt-4 mb-10">
        <img
          src="/public/LOGO REMAKE.png"
          alt="Logo GG"
          className="w-14 h-14"
        />
        <h1 className="font-bold text-xl sm:text-2xl md:text-4xl lg:text-5xl text-center text-orange-500 ml-2">
          FORM SERVICE CUSTOMER
        </h1>
      </div>
      <form className="w-full" onSubmit={handleSubmitQueue}>
        <div className="grid grid-cols-12">
          <div className="col-span-12 lg:col-span-10 lg:col-start-2 flex flex-col gap-6">
            <Card className="bg-neutral-900 border-neutral-800">
              <CardHeader>
                <CardTitle className="text-orange-500">
                  Identitas & Motor
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-8">
                {/* Cari Pelanggan */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-neutral-800 pb-6">
                  {/* Input + helper */}
                  <div className="flex-1 flex flex-col">
                    <Label className="text-neutral-400 mb-2">
                      Cari Pelanggan (No. WhatsApp / Plat Motor)
                    </Label>
                    <Input
                      type="text"
                      placeholder="Masukkan No. WhatsApp atau Plat Motor..."
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      required
                      disabled={step > 1}
                      className={inputClass}
                    />
                    <span className="mt-1 text-lg ml-2 text-red-500 italic">
                      No WhatsApp dimulai dengan 62...
                    </span>
                  </div>

                  {/* Tombol */}
                  <div className="flex-shrink-0 flex justify-center sm:justify-end items-center">
                    <Button
                      type="button"
                      onClick={handleSearchCustomer}
                      disabled={isLoading || step > 1}
                      className="w-full sm:w-auto bg-gray-700 hover:bg-gray-600"
                    >
                      {isLoading ? "Mencari..." : "Cari Pelanggan"}
                    </Button>
                  </div>
                </div>

                {/* Form Identitas & Motor */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                  {/* Nama */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">Nama</Label>
                    <Input
                      className={inputClass}
                      value={nama}
                      onChange={(e) => setNama(e.target.value.toUpperCase())}
                      required
                      disabled={step > 1 || isCustomerFound}
                    />
                  </div>

                  {/* No. WhatsApp */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">No. WhatsApp</Label>
                    <div className="flex rounded-md border border-neutral-700 bg-neutral-700 focus-within:ring-2 focus-within:ring-orange-500 focus-within:border-orange-500">
                      <span className="h-9 flex items-center px-3 text-neutral-400">
                        +62
                      </span>
                      <Input
                        type="number"
                        placeholder="85..."
                        value={noWA}
                        onChange={(e) => setNoWA(e.target.value)}
                        required
                        disabled={step > 1 || isCustomerFound}
                        className="flex-1 border-0 bg-transparent text-neutral-200 focus:ring-0 focus:border-none border-l-0 rounded-l-none"
                      />
                    </div>
                  </div>

                  {/* Plat Motor */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">Plat Motor</Label>
                    {isCustomerFound && foundCustomerMotors.length > 0 ? (
                      <Input
                        className={`${inputClass} cursor-not-allowed`}
                        value={plat}
                        readOnly
                        disabled={step > 1}
                      />
                    ) : (
                      <Input
                        className={inputClass}
                        value={plat}
                        onChange={(e) => setPlat(e.target.value.toUpperCase())}
                        required
                        placeholder="Plat Motor"
                        disabled={step > 1}
                      />
                    )}
                  </div>

                  {/* Nama Motor - Modified to use search */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">
                      {shockOnly ? "Shock Motor" : "Nama Motor"}
                    </Label>
                    {isCustomerFound && foundCustomerMotors.length > 0 ? (
                      <Select
                        onValueChange={(motorId) => {
                          const motor = foundCustomerMotors.find(
                            (m) => m.id.toString() === motorId
                          );
                          if (motor) {
                            setSavedMotor(motor);
                            setPlat(motor.plat_motor);
                            const motorFromMasterList = motors.find(
                              (m) => m.name === motor.nama_motor
                            );
                            setSelectedMotor(motorFromMasterList || null);
                            setSearchQueryMotor(motor.nama_motor);
                          }
                        }}
                        value={
                          savedMotor ? savedMotor.id.toString() : undefined
                        }
                        disabled={isLoading || step > 1}
                      >
                        <SelectTrigger className={triggerClass}>
                          <SelectValue placeholder="Pilih motor" />
                        </SelectTrigger>
                        <SelectContent className={contentClass}>
                          {foundCustomerMotors.map((m) => (
                            <SelectItem key={m.id} value={m.id.toString()}>
                              {m.nama_motor}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    ) : (
                      <div className="relative">
                        <Input
                          type="text"
                          placeholder="Cari nama motor..."
                          value={
                            activeDropdownMotor
                              ? searchQueryMotor
                              : selectedMotor?.name || ""
                          }
                          onChange={(e) => {
                            setSearchQueryMotor(e.target.value);
                            setActiveDropdownMotor(true);
                          }}
                          onFocus={() => setActiveDropdownMotor(true)}
                          onBlur={() => {
                            setTimeout(() => {
                              setActiveDropdownMotor(false);
                            }, 150);
                          }}
                          disabled={isLoading || step > 1}
                          className={inputClass}
                        />
                        {activeDropdownMotor && filteredMotors.length > 0 && (
                          <div className="absolute top-full left-0 z-30 w-full mt-1 bg-neutral-900 border border-neutral-800 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <div
                              className="p-2 cursor-pointer hover:bg-neutral-800 text-neutral-200"
                              onMouseDown={() => {
                                setSelectedMotor(null);
                                setSearchQueryMotor("");
                                setActiveDropdownMotor(false);
                              }}
                            >
                              <p>Batalkan</p>
                            </div>
                            {filteredMotors.map((motor) => (
                              <div
                                key={motor.id}
                                className="p-2 cursor-pointer hover:bg-neutral-800 text-neutral-200"
                                onMouseDown={() => {
                                  setSelectedMotor(motor);
                                  setSearchQueryMotor("");
                                  setActiveDropdownMotor(false);
                                  setSavedMotor(null);
                                }}
                              >
                                <p>{motor.name}</p>
                              </div>
                            ))}
                          </div>
                        )}
                      </div>
                    )}
                  </div>

                  {/* Jenis Motor */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">Jenis Motor</Label>
                    <Input
                      value={motorTypeName}
                      readOnly
                      className={`${inputClass} cursor-not-allowed`}
                    />
                  </div>

                  {/* Sumber Info */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">Sumber Info</Label>
                    <Select
                      onValueChange={setSumberInfo}
                      value={sumberInfo}
                      disabled={step > 1 || isCustomerFound}
                      required
                    >
                      <SelectTrigger className={triggerClass}>
                        <SelectValue placeholder="Pilih Sumber Info" />
                      </SelectTrigger>
                      <SelectContent className={contentClass}>
                        <SelectItem value="Instagram">Instagram</SelectItem>
                        <SelectItem value="Facebook">Facebook</SelectItem>
                        <SelectItem value="Youtube">Youtube</SelectItem>
                        <SelectItem value="Tiktok">Tiktok</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  {/* Sudah Chat Admin */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">
                      Sudah Chat Admin?
                    </Label>
                    <Select
                      onValueChange={setSudahChat}
                      value={sudahChat}
                      disabled={step > 1 || isCustomerFound}
                      required
                    >
                      <SelectTrigger className={triggerClass}>
                        <SelectValue placeholder="Pilih Status Chat" />
                      </SelectTrigger>
                      <SelectContent className={contentClass}>
                        <SelectItem value="Sudah Chat - IG">
                          Sudah Chat - IG
                        </SelectItem>
                        <SelectItem value="Sudah Chat - WA">
                          Sudah Chat - WA
                        </SelectItem>
                        <SelectItem value="Sudah Chat - Tiktok">
                          Sudah Chat - Tiktok
                        </SelectItem>
                        <SelectItem value="Datang Langsung">
                          Datang Langsung
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  {/* Lokasi Gerai */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">Lokasi Gerai</Label>
                    <Select
                      onValueChange={handleSelectGerai}
                      value={selectedGerai || ""}
                      required
                    >
                      <SelectTrigger className={triggerClass}>
                        <SelectValue placeholder="Pilih Gerai" />
                      </SelectTrigger>
                      <SelectContent className={contentClass}>
                        {gerais.map((gerai, index) => (
                          <SelectItem key={index} value={gerai.id.toString()}>
                            {gerai.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  {/* Shock Only */}
                  <div className="flex flex-col gap-2">
                    <Label className="text-neutral-400">Shock Only</Label>
                    <Select
                      onValueChange={(bool) => {
                        setShockOnly(bool == "true");
                      }}
                      value={shockOnly?.toString()}
                    >
                      <SelectTrigger className={triggerClass}>
                        <SelectValue placeholder="Apakah shock only?" />
                      </SelectTrigger>
                      <SelectContent className={contentClass}>
                        <SelectItem value={"true"}>Ya</SelectItem>
                        <SelectItem value={"false"}>Tidak</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </CardContent>
            </Card>

            {step === 2 && (
              <Card className="bg-neutral-900 border-neutral-800">
                <CardHeader>
                  <CardTitle className="text-orange-500">
                    Detail Layanan & Biaya
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid w-full gap-2 mb-6">
                    <Label className="text-neutral-400">
                      Pilih Tipe Servis
                    </Label>
                    <Select
                      onValueChange={setSelectedType}
                      value={selectedType || ""}
                      disabled={!selectedMotor || isLoading}
                    >
                      <SelectTrigger className={triggerClass}>
                        <SelectValue placeholder="Pilih tipe servis" />
                      </SelectTrigger>
                      <SelectContent className={contentClass}>
                        <SelectItem value="suspensi">Suspensi</SelectItem>
                        {!shockOnly && (
                          <>
                            <SelectItem value="komstir">Komstir</SelectItem>
                            <SelectItem value="komstir_suspensi">
                              Komstir + Suspensi
                            </SelectItem>
                          </>
                        )}
                      </SelectContent>
                    </Select>
                  </div>
                  {selectedType && (
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
                      {(selectedType === "suspensi" ||
                        selectedType === "komstir_suspensi") && (
                        <div className="grid w-full gap-3">
                          <Label className="text-neutral-400">
                            Layanan Suspensi
                          </Label>
                          <div className="relative">
                            <Input
                              type="text"
                              placeholder="Cari layanan suspensi..."
                              value={searchQuerySuspensi}
                              onChange={(e) => {
                                setSearchQuerySuspensi(e.target.value);
                                setActiveDropdownSuspensi(true);
                                setIsCheckedGaransi((prev) => ({
                                  ...prev,
                                  suspensi: false,
                                }));
                              }}
                              onFocus={() => setActiveDropdownSuspensi(true)}
                              onBlur={() => {
                                setTimeout(() => {
                                  setActiveDropdownSuspensi(false);
                                }, 150);
                              }}
                              className={inputClass}
                            />
                            {activeDropdownSuspensi &&
                              filteredLayananSuspensi.length > 0 && (
                                <div className="absolute top-full left-0 z-30 w-full mt-1 bg-neutral-900 border border-neutral-800 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                  {filteredLayananSuspensi.map((s) => (
                                    <div
                                      key={s.id}
                                      className="p-2 cursor-pointer hover:bg-neutral-800 text-neutral-200"
                                      onMouseDown={() => {
                                        const option = layananSuspensi.find(
                                          (o) =>
                                            o.id.toString() === s.id.toString()
                                        );
                                        if (
                                          option &&
                                          !selectedSuspensi.some(
                                            (item) => item.id === option.id
                                          )
                                        ) {
                                          setSelectedSuspensi((prev) => [
                                            ...prev,
                                            option,
                                          ]);
                                        }
                                        setIsCheckedGaransi((prev) => ({
                                          ...prev,
                                          suspensi: false,
                                        }));
                                        setSearchQuerySuspensi("");
                                        setActiveDropdownSuspensi(false);
                                      }}
                                    >
                                      <p>{s.name}</p>
                                      <span className="text-neutral-400 flex gap-2">
                                        <span>{s.cc_range}</span>-
                                        <span>
                                          Rp{" "}
                                          {s.total_price.toLocaleString(
                                            "id-ID"
                                          )}
                                        </span>
                                      </span>
                                    </div>
                                  ))}
                                </div>
                              )}
                          </div>

                          {selectedSuspensi.length > 0 && (
                            <div className="flex flex-wrap gap-2 pt-2">
                              {selectedSuspensi.map((item) => (
                                <Badge
                                  key={item.id}
                                  className="bg-orange-600 hover:bg-orange-700 text-white pl-3 pr-1 py-1 text-sm"
                                >
                                  {item.name} - {item.cc_range}
                                  <button
                                    type="button"
                                    className="ml-2 rounded-full hover:bg-black/20 p-0.5"
                                    onClick={() =>
                                      setSelectedSuspensi((prev) =>
                                        prev.filter((s) => s.id !== item.id)
                                      )
                                    }
                                  >
                                    <X size={14} />
                                  </button>
                                </Badge>
                              ))}
                            </div>
                          )}
                          {/* Klaim Garansi Suspensi */}
                          {selectedSuspensi.length > 0 && (
                            <div className="flex gap-2 items-center">
                              <Input
                                checked={isCheckedGaransi.suspensi}
                                onChange={handleClaimGaransiCheckSuspensi}
                                id="suspensi_klaim_garansi"
                                type="checkbox"
                                className="w-3 h-3 "
                              />
                              <Label
                                htmlFor="suspensi_klaim_garansi"
                                className="text-neutral-400"
                              >
                                Klaim Garansi
                              </Label>
                            </div>
                          )}
                        </div>
                      )}
                      {(selectedType === "komstir" ||
                        selectedType === "komstir_suspensi") && (
                        <div className="grid w-full gap-2">
                          <Label className="text-neutral-400">
                            Layanan Komstir
                          </Label>
                          <div className="relative">
                            <Input
                              type="text"
                              placeholder="Cari layanan komstir..."
                              value={
                                activeDropdownKomstir
                                  ? searchQueryKomstir
                                  : selectedLayananKomstir?.name || ""
                              }
                              onChange={(e) => {
                                setSearchQueryKomstir(e.target.value);
                                setActiveDropdownKomstir(true);
                              }}
                              onFocus={() => setActiveDropdownKomstir(true)}
                              onBlur={() => {
                                setTimeout(() => {
                                  setActiveDropdownKomstir(false);
                                }, 150);
                              }}
                              className={inputClass}
                            />
                            {activeDropdownKomstir &&
                              filteredLayananKomstir.length > 0 && (
                                <div className="absolute top-full left-0 z-30 w-full mt-1 bg-neutral-900 border border-neutral-800 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                  <div
                                    className="p-2 cursor-pointer hover:bg-neutral-800 text-neutral-200"
                                    onMouseDown={() => {
                                      setSelectedLayananKomstir(null);
                                      setSearchQueryKomstir("");
                                      setActiveDropdownKomstir(false);
                                    }}
                                  >
                                    <p>Batalkan</p>
                                  </div>
                                  {filteredLayananKomstir.map((k) => (
                                    <div
                                      key={k.id}
                                      className="p-2 cursor-pointer hover:bg-neutral-800 text-neutral-200"
                                      onMouseDown={() => {
                                        setSelectedLayananKomstir(k);
                                        setSearchQueryKomstir("");
                                        setActiveDropdownKomstir(false);
                                      }}
                                    >
                                      <p>{k.name}</p>
                                      <p className="text-xs text-neutral-400">
                                        Rp{" "}
                                        {k.total_price.toLocaleString("id-ID")}
                                      </p>
                                    </div>
                                  ))}
                                </div>
                              )}
                          </div>
                          {/* Klaim Garansi Komstir */}
                          {selectedLayananKomstir && (
                            <div className="flex gap-2 items-center">
                              <Input
                                value={isCheckedGaransi.komstir.toString()}
                                onChange={handleClaimGaransiCheckKomstir}
                                id="komstir_klaim_garansi"
                                type="checkbox"
                                className="w-3 h-3 "
                              />
                              <Label
                                htmlFor="komstir_klaim_garansi"
                                className="text-neutral-400"
                              >
                                Klaim Garansi
                              </Label>
                            </div>
                          )}
                        </div>
                      )}
                    </div>
                  )}

                  <div className="grid w-full gap-2 mt-6">
                    <Label className="text-lg font-bold text-white">
                      Total Estimasi Harga
                    </Label>
                    <div className="flex items-center">
                      <span className="h-10 flex items-center px-3 bg-neutral-700 border border-r-0 border-neutral-600 rounded-l-md">
                        Rp.
                      </span>
                      <Input
                        type="text"
                        value={totalHarga.toLocaleString("id-ID")}
                        readOnly={false}
                        onChange={(e) => setTotalHarga(e.target.value as any)}
                        className={`${inputClass} rounded-l-none font-bold text-lg text-orange-400`}
                      />
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}

            <div className="flex flex-col sm:flex-row gap-4 mt-4">
              {step === 1 && (
                <Button
                  className="bg-orange-600 hover:bg-orange-700 text-white w-full sm:w-auto"
                  type="button"
                  onClick={handleSaveCustomerAndMotor}
                  disabled={
                    isLoading ||
                    isSubmitting ||
                    !selectedMotor ||
                    !selectedGerai ||
                    shockOnly == undefined
                  }
                >
                  {isSubmitting ? "Menyimpan..." : "Lanjutkan ke Pilih Layanan"}
                </Button>
              )}
              {step === 2 && (
                <Button
                  className="bg-orange-600 hover:bg-orange-700 text-white w-full sm:w-auto"
                  type="submit"
                  disabled={isSubmitting || !selectedType}
                >
                  {isSubmitting ? "Menyimpan..." : "Simpan & Masuk Antrian"}
                </Button>
              )}
              <Button
                className="bg-gray-600 hover:bg-gray-700 text-white w-full sm:w-auto"
                type="button"
                onClick={() => (step === 1 ? navigate(-1) : setStep(1))}
              >
                {step === 1 ? "Batal" : "Kembali"}
              </Button>
            </div>
          </div>
        </div>
      </form>
    </div>
  );
}