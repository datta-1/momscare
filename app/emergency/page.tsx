'use client';
import React, { useEffect, useState } from "react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Phone, MapPin, Clock, AlertTriangle, Stethoscope, Heart, Baby, Car, User } from "lucide-react";
import { motion } from "framer-motion";
import { getCurrentUser, getEmergencyContacts, addEmergencyContact } from "@/lib/appwrite";
import { toast, ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

interface EmergencyContact {
  id: string;
  name: string;
  phone: string;
  relationship: string;
  addedAt: string;
}

export default function Emergency() {
  const [currentLocation, setCurrentLocation] = useState(null);
  const [hospitals, setHospitals] = useState([]);
  const [locationError, setLocationError] = useState(null);
  const [emergencyContacts, setEmergencyContacts] = useState<EmergencyContact[]>([]);
  const [user, setUser] = useState<any>(null);
  const [newContact, setNewContact] = useState({ name: '', phone: '', relationship: '' });
  const [isAddingContact, setIsAddingContact] = useState(false);

  const emergencyNumbers = [
    { name: "Emergency Services", number: "102", description: "General emergency services" },
    { name: "Medical Emergency", number: "108", description: "Ambulance and medical emergency" },
    { name: "Police", number: "100", description: "Police emergency" },
    { name: "Fire Service", number: "101", description: "Fire department" },
    { name: "Women Helpline", number: "1091", description: "24/7 women emergency helpline" },
    { name: "Pregnancy Support", number: "0444-631-4300", description: "24/7 pregnancy support hotline" }
  ];

  const warningSignsByTrimester = {
    first: [
      "Severe abdominal cramping",
      "Heavy vaginal bleeding",
      "Severe nausea preventing food/water intake",
      "High fever (over 101°F)",
      "Severe headache with vision changes"
    ],
    second: [
      "Decreased or no fetal movement",
      "Severe abdominal pain",
      "Water breaking before 37 weeks",
      "Persistent severe headache",
      "Sudden severe swelling in face/hands"
    ],
    third: [
      "Regular contractions before 37 weeks",
      "Water breaking",
      "Severe decrease in fetal movement",
      "Heavy bleeding",
      "Severe chest pain or trouble breathing"
    ]
  };

  useEffect(() => {
    loadUserData();
    requestLocation();
  }, []);

  const loadUserData = async () => {
    try {
      const currentUser = await getCurrentUser();
      if (currentUser) {
        setUser(currentUser);
        const contacts = await getEmergencyContacts();
        setEmergencyContacts(contacts);
      }
    } catch (error) {
      console.error('Error loading user data:', error);
    }
  };

  const requestLocation = () => {
    setLocationError(null);
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setCurrentLocation({
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          });
        },
        (error) => {
          setLocationError(
            "Unable to retrieve your location. Please allow location access."
          );
        }
      );
    } else {
      setLocationError("Geolocation is not supported by your browser.");
    }
  };

  const loadGoogleMapsScript = (callback) => {
    if (window.google && window.google.maps) {
      callback();
      return;
    }
    const script = document.createElement("script");
    script.src = `https://maps.googleapis.com/maps/api/js?key=${process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY}&libraries=places`;
    script.async = true;
    script.defer = true;
    script.onload = callback;
    document.body.appendChild(script);
  };

  useEffect(() => {
    if (currentLocation) {
      loadGoogleMapsScript(() => {
        const location = new window.google.maps.LatLng(
          currentLocation.lat,
          currentLocation.lng
        );
        const service = new window.google.maps.places.PlacesService(
          document.createElement("div")
        );
        const request = {
          location: location,
          rankBy: window.google.maps.places.RankBy.DISTANCE,
          type: "hospital",
          keyword: "maternity hospital emergency obstetrics gynecology"
        };

        service.nearbySearch(request, (results, status) => {
          if (
            status === window.google.maps.places.PlacesServiceStatus.OK &&
            results
          ) {
            setHospitals(results);
          }
        });
      });
    }
  }, [currentLocation]);

  const handleAddEmergencyContact = async () => {
    if (!newContact.name || !newContact.phone || !newContact.relationship) {
      toast.error("Please fill in all fields");
      return;
    }

    setIsAddingContact(true);
    try {
      await addEmergencyContact(newContact);
      toast.success("Emergency contact added successfully!");
      setNewContact({ name: '', phone: '', relationship: '' });
      loadUserData(); // Reload contacts
    } catch (error) {
      toast.error("Failed to add emergency contact");
      console.error(error);
    } finally {
      setIsAddingContact(false);
    }
  };

  const generateMapLink = (placeId) =>
    `https://www.google.com/maps/search/?api=1&query_place_id=${placeId}`;

  const callEmergency = (number) => {
    window.location.href = `tel:${number}`;
  };

  return (
    <div className="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-red-50 to-pink-50">
      <ToastContainer position="top-right" autoClose={3000} />
      
      <div className="max-w-7xl mx-auto">
        {/* Emergency Alert */}
        <Alert className="mb-8 border-red-500 bg-red-50">
          <AlertTriangle className="h-4 w-4" />
          <AlertDescription className="text-red-800 font-medium">
            If you are experiencing a medical emergency, call emergency services immediately!
          </AlertDescription>
        </Alert>

        {/* Header */}
        <motion.div 
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-center mb-12"
        >
          <h1 className="text-4xl font-bold text-gray-900 mb-4">Emergency Support</h1>
          <p className="text-xl text-gray-600">Quick access to emergency services and important information</p>
        </motion.div>

        <Tabs defaultValue="emergency" className="space-y-8">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="emergency" className="flex items-center gap-2">
              <Phone className="w-4 h-4" />
              Emergency
            </TabsTrigger>
            <TabsTrigger value="hospitals" className="flex items-center gap-2">
              <MapPin className="w-4 h-4" />
              Hospitals
            </TabsTrigger>
            <TabsTrigger value="warnings" className="flex items-center gap-2">
              <AlertTriangle className="w-4 h-4" />
              Warning Signs
            </TabsTrigger>
            <TabsTrigger value="contacts" className="flex items-center gap-2">
              <User className="w-4 h-4" />
              My Contacts
            </TabsTrigger>
          </TabsList>

          <TabsContent value="emergency" className="space-y-6">
            {/* Quick Actions */}
            <Card className="bg-red-50 border-red-200">
              <CardHeader>
                <CardTitle className="text-red-800 flex items-center gap-2">
                  <Phone className="w-5 h-5" />
                  Emergency Quick Actions
                </CardTitle>
                <CardDescription>Tap to call emergency services immediately</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <Button 
                    size="lg" 
                    className="h-16 bg-red-600 hover:bg-red-700 text-white"
                    onClick={() => callEmergency("102")}
                  >
                    <Phone className="w-6 h-6 mr-2" />
                    Call 102 - Emergency
                  </Button>
                  <Button 
                    size="lg" 
                    className="h-16 bg-blue-600 hover:bg-blue-700 text-white"
                    onClick={() => callEmergency("108")}
                  >
                    <Car className="w-6 h-6 mr-2" />
                    Call 108 - Ambulance
                  </Button>
                </div>
              </CardContent>
            </Card>

            {/* Emergency Numbers */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Phone className="w-5 h-5" />
                  Emergency Numbers
                </CardTitle>
                <CardDescription>Important phone numbers for various emergencies</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {emergencyNumbers.map((emergency, index) => (
                    <motion.div
                      key={index}
                      initial={{ opacity: 0, x: -20 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: index * 0.1 }}
                    >
                      <Card className="hover:shadow-md transition-shadow cursor-pointer" 
                            onClick={() => callEmergency(emergency.number)}>
                        <CardContent className="p-4">
                          <div className="flex justify-between items-center">
                            <div>
                              <h4 className="font-semibold">{emergency.name}</h4>
                              <p className="text-sm text-gray-600">{emergency.description}</p>
                            </div>
                            <Badge variant="destructive" className="text-lg px-3 py-1">
                              {emergency.number}
                            </Badge>
                          </div>
                        </CardContent>
                      </Card>
                    </motion.div>
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* First Aid Tips */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Heart className="w-5 h-5" />
                  Emergency First Aid for Pregnancy
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="p-4 bg-blue-50 rounded-lg">
                    <h4 className="font-semibold text-blue-800 mb-2">Heavy Bleeding</h4>
                    <ul className="text-sm text-blue-700 list-disc list-inside">
                      <li>Lie down immediately</li>
                      <li>Call emergency services</li>
                      <li>Do not use tampons</li>
                      <li>Monitor vital signs</li>
                    </ul>
                  </div>
                  <div className="p-4 bg-green-50 rounded-lg">
                    <h4 className="font-semibold text-green-800 mb-2">Severe Abdominal Pain</h4>
                    <ul className="text-sm text-green-700 list-disc list-inside">
                      <li>Find a comfortable position</li>
                      <li>Apply gentle warmth (if tolerated)</li>
                      <li>Monitor for other symptoms</li>
                      <li>Seek immediate medical attention</li>
                    </ul>
                  </div>
                  <div className="p-4 bg-purple-50 rounded-lg">
                    <h4 className="font-semibold text-purple-800 mb-2">Preterm Labor</h4>
                    <ul className="text-sm text-purple-700 list-disc list-inside">
                      <li>Time contractions</li>
                      <li>Change positions</li>
                      <li>Drink water</li>
                      <li>Contact healthcare provider immediately</li>
                    </ul>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="hospitals" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <MapPin className="w-5 h-5" />
                  Nearby Hospitals
                </CardTitle>
                <CardDescription>Maternity and emergency hospitals near your location</CardDescription>
              </CardHeader>
              <CardContent>
                {locationError && (
                  <div className="text-center py-8">
                    <p className="text-red-500 mb-4">{locationError}</p>
                    <Button onClick={requestLocation} variant="outline">
                      <MapPin className="w-4 h-4 mr-2" />
                      Allow Location Access
                    </Button>
                  </div>
                )}
                {!locationError && !currentLocation && (
                  <div className="text-center py-8">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto mb-4"></div>
                    <p>Loading your location...</p>
                  </div>
                )}
                {!locationError && currentLocation && hospitals.length === 0 && (
                  <div className="text-center py-8">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto mb-4"></div>
                    <p>Finding nearby hospitals...</p>
                  </div>
                )}
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {hospitals.slice(0, 8).map((hospital, index) => (
                    <motion.div
                      key={index}
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: index * 0.1 }}
                    >
                      <Card className="hover:shadow-md transition-shadow">
                        <CardContent className="p-4">
                          <div className="flex justify-between items-start mb-3">
                            <div className="flex-1">
                              <h4 className="font-semibold text-lg">{hospital.name}</h4>
                              <p className="text-gray-600 text-sm">{hospital.vicinity}</p>
                              {hospital.rating && (
                                <div className="flex items-center mt-1">
                                  <span className="text-yellow-500">★</span>
                                  <span className="text-sm ml-1">{hospital.rating}</span>
                                </div>
                              )}
                            </div>
                            <Badge variant={hospital.opening_hours?.open_now ? "default" : "secondary"}>
                              {hospital.opening_hours?.open_now ? "Open" : "Check Hours"}
                            </Badge>
                          </div>
                          <div className="flex gap-2">
                            <Button
                              size="sm"
                              className="flex-1"
                              onClick={() => window.open(generateMapLink(hospital.place_id), '_blank')}
                            >
                              <MapPin className="w-4 h-4 mr-1" />
                              Directions
                            </Button>
                            {hospital.formatted_phone_number && (
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => callEmergency(hospital.formatted_phone_number)}
                              >
                                <Phone className="w-4 h-4" />
                              </Button>
                            )}
                          </div>
                        </CardContent>
                      </Card>
                    </motion.div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="warnings" className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {Object.entries(warningSignsByTrimester).map(([trimester, signs], index) => (
                <motion.div
                  key={trimester}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.2 }}
                >
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <Baby className="w-5 h-5" />
                        {trimester.charAt(0).toUpperCase() + trimester.slice(1)} Trimester
                      </CardTitle>
                      <CardDescription>Warning signs to watch for</CardDescription>
                    </CardHeader>
                    <CardContent>
                      <ul className="space-y-2">
                        {signs.map((sign, signIndex) => (
                          <li key={signIndex} className="flex items-start gap-2">
                            <AlertTriangle className="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" />
                            <span className="text-sm">{sign}</span>
                          </li>
                        ))}
                      </ul>
                    </CardContent>
                  </Card>
                </motion.div>
              ))}
            </div>

            <Card className="bg-yellow-50 border-yellow-200">
              <CardHeader>
                <CardTitle className="text-yellow-800">When to Call Your Doctor</CardTitle>
              </CardHeader>
              <CardContent className="text-yellow-700">
                <p className="mb-4">
                  Contact your healthcare provider immediately if you experience any of the warning signs above, or if you have:
                </p>
                <ul className="list-disc list-inside space-y-1">
                  <li>Persistent concerns about your baby's movements</li>
                  <li>Questions about symptoms you're experiencing</li>
                  <li>Anxiety about your pregnancy that affects daily life</li>
                  <li>Any situation that doesn't feel right to you</li>
                </ul>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="contacts" className="space-y-6">
            {user ? (
              <>
                {/* Add New Contact */}
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <User className="w-5 h-5" />
                      Add Emergency Contact
                    </CardTitle>
                    <CardDescription>Add people who should be contacted in case of emergency</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                      <input
                        type="text"
                        placeholder="Full Name"
                        value={newContact.name}
                        onChange={(e) => setNewContact(prev => ({ ...prev, name: e.target.value }))}
                        className="px-3 py-2 border rounded-md"
                      />
                      <input
                        type="tel"
                        placeholder="Phone Number"
                        value={newContact.phone}
                        onChange={(e) => setNewContact(prev => ({ ...prev, phone: e.target.value }))}
                        className="px-3 py-2 border rounded-md"
                      />
                      <select
                        value={newContact.relationship}
                        onChange={(e) => setNewContact(prev => ({ ...prev, relationship: e.target.value }))}
                        className="px-3 py-2 border rounded-md"
                      >
                        <option value="">Select Relationship</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Friend">Friend</option>
                        <option value="Doctor">Doctor</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>
                    <Button 
                      onClick={handleAddEmergencyContact}
                      disabled={isAddingContact}
                      className="w-full md:w-auto"
                    >
                      {isAddingContact ? 'Adding...' : 'Add Contact'}
                    </Button>
                  </CardContent>
                </Card>

                {/* Emergency Contacts List */}
                <Card>
                  <CardHeader>
                    <CardTitle>Your Emergency Contacts</CardTitle>
                    <CardDescription>People who will be contacted in case of emergency</CardDescription>
                  </CardHeader>
                  <CardContent>
                    {emergencyContacts.length === 0 ? (
                      <div className="text-center py-8">
                        <User className="w-12 h-12 mx-auto text-gray-400 mb-4" />
                        <h3 className="text-lg font-semibold mb-2">No Emergency Contacts</h3>
                        <p className="text-gray-600">Add emergency contacts to ensure help is available when needed</p>
                      </div>
                    ) : (
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {emergencyContacts.map((contact, index) => (
                          <motion.div
                            key={contact.id}
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{ delay: index * 0.1 }}
                          >
                            <Card className="hover:shadow-md transition-shadow">
                              <CardContent className="p-4">
                                <div className="flex justify-between items-start">
                                  <div className="flex-1">
                                    <h4 className="font-semibold">{contact.name}</h4>
                                    <p className="text-sm text-gray-600">{contact.relationship}</p>
                                    <p className="text-lg font-mono">{contact.phone}</p>
                                  </div>
                                  <Button
                                    size="sm"
                                    onClick={() => callEmergency(contact.phone)}
                                  >
                                    <Phone className="w-4 h-4" />
                                  </Button>
                                </div>
                              </CardContent>
                            </Card>
                          </motion.div>
                        ))}
                      </div>
                    )}
                  </CardContent>
                </Card>
              </>
            ) : (
              <Card>
                <CardContent className="p-8 text-center">
                  <User className="w-12 h-12 mx-auto text-gray-400 mb-4" />
                  <h3 className="text-lg font-semibold mb-2">Login Required</h3>
                  <p className="text-gray-600 mb-4">Please log in to manage your emergency contacts</p>
                  <Button asChild>
                    <a href="/login">Login</a>
                  </Button>
                </CardContent>
              </Card>
            )}
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
