"use client";

import { useEffect, useRef, useState, ChangeEvent } from "react";
import { getCurrentUser, uploadProfilePhoto, updateUserProfilePhoto, getHealthRecords, addHealthRecord, listAppointments } from "@/lib/appwrite";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Progress } from "@/components/ui/progress";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ToastContainer, toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import Link from "next/link";
import { Calendar, Heart, Activity, Weight, Pill, Clock, User, Plus, TrendingUp, Baby, BookOpen } from "lucide-react";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

interface Profile {
  name: string;
  dob: string;
  address: string;
  email: string;
  phone: string;
  imageUrl?: string;
  weeksPregnant?: number;
  dueDate?: string;
  expectedDeliveryDate?: string;
}

interface HealthRecord {
  $id: string;
  type: string;
  value: number;
  unit: string;
  recordedAt: string;
  notes?: string;
}

interface Appointment {
  $id: string;
  date: string;
  time: string;
  type: string;
  doctor: string;
  notes?: string;
}

export default function Dashboard() {
  const [profile, setProfile] = useState<Profile | null>(null);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const [healthRecords, setHealthRecords] = useState<HealthRecord[]>([]);
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [newHealthRecord, setNewHealthRecord] = useState({
    type: 'weight',
    value: '',
    notes: ''
  });
  const fileInputRef = useRef<HTMLInputElement | null>(null);

  // Calculate pregnancy progress
  const calculatePregnancyWeek = () => {
    if (!profile?.weeksPregnant) return { week: 0, progress: 0 };
    const week = Math.floor(profile.weeksPregnant);
    const progress = (week / 40) * 100;
    return { week, progress };
  };

  const calculateTrimester = () => {
    if (!profile?.weeksPregnant) return "First";
    if (profile.weeksPregnant <= 13) return "First";
    if (profile.weeksPregnant <= 27) return "Second";
    return "Third";
  };

  useEffect(() => {
    async function loadDashboardData() {
      try {
        const user = await getCurrentUser();
        if (user) {
          setProfile({
            name: user.name || "",
            dob: user.prefs?.dob || "",
            address: user.prefs?.address || "",
            email: user.email || "",
            phone: user.phone || "",
            imageUrl: user.prefs?.imageUrl || "",
            weeksPregnant: user.prefs?.weeksPregnant || 20,
            dueDate: user.prefs?.dueDate || "",
            expectedDeliveryDate: user.prefs?.expectedDeliveryDate || ""
          });
          setImagePreview(user.prefs?.imageUrl || null);

          // Load health records
          const healthData = await getHealthRecords();
          setHealthRecords(healthData.documents || []);

          // Load appointments
          const appointmentsData = await listAppointments();
          setAppointments(appointmentsData.documents || []);
        } else {
          setProfile(null);
        }
      } catch (error) {
        toast.error("Error loading dashboard data.");
        console.error("Error loading dashboard data:", error);
        setProfile(null);
      } finally {
        setLoading(false);
      }
    }
    loadDashboardData();
  }, []);

  const handleImageUpload = async (e: ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];

      const allowedFormats = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
      if (!allowedFormats.includes(file.type)) {
        toast.error("Invalid file format. Only PNG, JPG, JPEG, and WEBP are allowed.");
        return;
      }

      setUploading(true);
      toast.info("Uploading profile picture...");
      
      const uploadResponse = await uploadProfilePhoto(file);
      if (uploadResponse && uploadResponse.$id) {
        const fileUrl = `${process.env.NEXT_PUBLIC_APPWRITE_ENDPOINT}/storage/buckets/${process.env.NEXT_PUBLIC_APPWRITE_PROFILE_BUCKET_ID}/files/${uploadResponse.$id}/view?project=${process.env.NEXT_PUBLIC_APPWRITE_PROJECT_ID}&mode=admin`;
        setImagePreview(fileUrl);
        
        const updatedUser = await updateUserProfilePhoto(fileUrl);
        if (updatedUser) {
          setProfile(prev => prev ? { ...prev, imageUrl: fileUrl } : prev);
          toast.success("Profile picture updated successfully!");
        }
      } else {
        toast.error("Failed to upload profile picture.");
      }
      setUploading(false);
    }
  };

  const handleAddHealthRecord = async () => {
    if (!newHealthRecord.value) {
      toast.error("Please enter a value");
      return;
    }

    try {
      const record = await addHealthRecord({
        type: newHealthRecord.type,
        value: parseFloat(newHealthRecord.value),
        unit: getUnitForType(newHealthRecord.type),
        notes: newHealthRecord.notes
      });

      setHealthRecords(prev => [record, ...prev]);
      setNewHealthRecord({ type: 'weight', value: '', notes: '' });
      toast.success("Health record added successfully!");
    } catch (error) {
      toast.error("Error adding health record");
      console.error(error);
    }
  };

  const getUnitForType = (type: string) => {
    switch (type) {
      case 'weight': return 'kg';
      case 'bloodPressure': return 'mmHg';
      case 'bloodSugar': return 'mg/dL';
      case 'heartRate': return 'bpm';
      default: return '';
    }
  };

  const getChartData = (type: string) => {
    return healthRecords
      .filter(record => record.type === type)
      .slice(0, 10)
      .reverse()
      .map(record => ({
        date: new Date(record.recordedAt).toLocaleDateString(),
        value: record.value,
        fullDate: record.recordedAt
      }));
  };

  const getUpcomingAppointments = () => {
    return appointments
      .filter(apt => new Date(apt.date) >= new Date())
      .slice(0, 3);
  };

  const pregnancyTips = [
    "Take prenatal vitamins daily",
    "Stay hydrated - drink 8-10 glasses of water",
    "Get adequate sleep (7-9 hours)",
    "Do gentle exercises like walking or prenatal yoga",
    "Eat small, frequent meals"
  ];

  if (loading) {
    return <p className="text-center mt-8">Loading dashboard...</p>;
  }

  if (!profile) {
    return (
      <div className="flex items-center justify-center min-h-screen p-4">
        <div className="bg-white rounded-xl shadow-lg p-8 max-w-md w-full text-center">
          <h1 className="text-2xl font-bold text-gray-800 mb-2">Access Denied</h1>
          <p className="text-gray-600 mb-6">Wrong Credentials. Please check your details and try again.</p>
          <Link href="/login">
            <Button className="w-full py-2 mb-4 bg-blue-600 hover:bg-blue-500 transition duration-200">
              Login
            </Button>
          </Link>
          <p className="text-gray-600">
            New here?{" "}
            <Link href="/signup" className="text-blue-500 font-semibold hover:underline">
              Register here
            </Link>
          </p>
        </div>
      </div>
    );
  }

  const { week, progress } = calculatePregnancyWeek();
  const trimester = calculateTrimester();

  return (
    <div className="min-h-screen p-6 bg-gradient-to-br from-pink-50 to-purple-50">
      <ToastContainer position="top-right" autoClose={3000} />
      
      {/* Header */}
      <div className="max-w-7xl mx-auto mb-8">
        <div className="flex flex-col md:flex-row items-center justify-between bg-white rounded-xl shadow-lg p-6">
          <div className="flex items-center space-x-4">
            <div className="relative">
              <div className="w-20 h-20 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                {imagePreview || profile.imageUrl ? (
                  <img
                    src={imagePreview || profile.imageUrl}
                    alt="Profile"
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <User className="w-8 h-8 text-gray-500" />
                )}
              </div>
              <Button
                size="sm"
                className="absolute -bottom-2 -right-2 rounded-full w-8 h-8 p-0"
                onClick={() => fileInputRef.current?.click()}
                disabled={uploading}
              >
                <Plus className="w-4 h-4" />
              </Button>
              <input
                type="file"
                accept="image/png, image/jpeg, image/jpg, image/webp"
                onChange={handleImageUpload}
                ref={fileInputRef}
                style={{ display: 'none' }}
              />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-800">Welcome back, {profile.name}!</h1>
              <p className="text-gray-600">Week {week} • {trimester} Trimester</p>
            </div>
          </div>
          <Badge variant="secondary" className="text-lg px-4 py-2">
            <Baby className="w-4 h-4 mr-2" />
            {40 - week} weeks to go
          </Badge>
        </div>
      </div>

      <div className="max-w-7xl mx-auto">
        {/* Pregnancy Progress */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center">
              <Activity className="w-5 h-5 mr-2" />
              Pregnancy Progress
            </CardTitle>
            <CardDescription>Track your journey to motherhood</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="font-medium">Week {week} of 40</span>
                <span className="text-sm text-gray-500">{progress.toFixed(1)}% Complete</span>
              </div>
              <Progress value={progress} className="h-3" />
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div className="text-center p-4 bg-pink-50 rounded-lg">
                  <div className="text-2xl font-bold text-pink-600">{week}</div>
                  <div className="text-sm text-gray-600">Weeks</div>
                </div>
                <div className="text-center p-4 bg-purple-50 rounded-lg">
                  <div className="text-2xl font-bold text-purple-600">{trimester}</div>
                  <div className="text-sm text-gray-600">Trimester</div>
                </div>
                <div className="text-center p-4 bg-blue-50 rounded-lg">
                  <div className="text-2xl font-bold text-blue-600">{40 - week}</div>
                  <div className="text-sm text-gray-600">Weeks to go</div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Tabs defaultValue="overview" className="space-y-4">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="health">Health Records</TabsTrigger>
            <TabsTrigger value="appointments">Appointments</TabsTrigger>
            <TabsTrigger value="tips">Tips & Resources</TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {/* Quick Stats */}
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Next Appointment</CardTitle>
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  {getUpcomingAppointments().length > 0 ? (
                    <div>
                      <div className="text-2xl font-bold">
                        {new Date(getUpcomingAppointments()[0].date).toLocaleDateString()}
                      </div>
                      <p className="text-xs text-muted-foreground">
                        {getUpcomingAppointments()[0].type}
                      </p>
                    </div>
                  ) : (
                    <div>
                      <div className="text-2xl font-bold">No upcoming</div>
                      <p className="text-xs text-muted-foreground">Schedule an appointment</p>
                    </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Latest Weight</CardTitle>
                  <Weight className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  {healthRecords.filter(r => r.type === 'weight')[0] ? (
                    <div>
                      <div className="text-2xl font-bold">
                        {healthRecords.filter(r => r.type === 'weight')[0].value} kg
                      </div>
                      <p className="text-xs text-muted-foreground">
                        Recorded {new Date(healthRecords.filter(r => r.type === 'weight')[0].recordedAt).toLocaleDateString()}
                      </p>
                    </div>
                  ) : (
                    <div>
                      <div className="text-2xl font-bold">--</div>
                      <p className="text-xs text-muted-foreground">No records yet</p>
                    </div>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Blood Pressure</CardTitle>
                  <Heart className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  {healthRecords.filter(r => r.type === 'bloodPressure')[0] ? (
                    <div>
                      <div className="text-2xl font-bold">
                        {healthRecords.filter(r => r.type === 'bloodPressure')[0].value}
                      </div>
                      <p className="text-xs text-muted-foreground">mmHg</p>
                    </div>
                  ) : (
                    <div>
                      <div className="text-2xl font-bold">--</div>
                      <p className="text-xs text-muted-foreground">No records yet</p>
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>

            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
                <CardDescription>Manage your pregnancy journey</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <Link href="/chat">
                    <Button className="w-full h-20 flex flex-col">
                      <Activity className="w-6 h-6 mb-2" />
                      Chat with AI
                    </Button>
                  </Link>
                  <Link href="/appointments">
                    <Button variant="outline" className="w-full h-20 flex flex-col">
                      <Calendar className="w-6 h-6 mb-2" />
                      Book Appointment
                    </Button>
                  </Link>
                  <Link href="/medicaldocuments">
                    <Button variant="outline" className="w-full h-20 flex flex-col">
                      <BookOpen className="w-6 h-6 mb-2" />
                      Medical Docs
                    </Button>
                  </Link>
                  <Link href="/emergency">
                    <Button variant="destructive" className="w-full h-20 flex flex-col">
                      <Pill className="w-6 h-6 mb-2" />
                      Emergency
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="health" className="space-y-6">
            {/* Add Health Record */}
            <Card>
              <CardHeader>
                <CardTitle>Add Health Record</CardTitle>
                <CardDescription>Track your health metrics</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <select
                    value={newHealthRecord.type}
                    onChange={(e) => setNewHealthRecord(prev => ({ ...prev, type: e.target.value }))}
                    className="px-3 py-2 border rounded-md"
                  >
                    <option value="weight">Weight</option>
                    <option value="bloodPressure">Blood Pressure</option>
                    <option value="bloodSugar">Blood Sugar</option>
                    <option value="heartRate">Heart Rate</option>
                  </select>
                  <Input
                    type="number"
                    placeholder="Value"
                    value={newHealthRecord.value}
                    onChange={(e) => setNewHealthRecord(prev => ({ ...prev, value: e.target.value }))}
                  />
                  <Input
                    placeholder="Notes (optional)"
                    value={newHealthRecord.notes}
                    onChange={(e) => setNewHealthRecord(prev => ({ ...prev, notes: e.target.value }))}
                  />
                  <Button onClick={handleAddHealthRecord}>Add Record</Button>
                </div>
              </CardContent>
            </Card>

            {/* Health Charts */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Weight Tracking</CardTitle>
                </CardHeader>
                <CardContent>
                  <ResponsiveContainer width="100%" height={300}>
                    <LineChart data={getChartData('weight')}>
                      <CartesianGrid strokeDasharray="3 3" />
                      <XAxis dataKey="date" />
                      <YAxis />
                      <Tooltip />
                      <Line type="monotone" dataKey="value" stroke="#8884d8" strokeWidth={2} />
                    </LineChart>
                  </ResponsiveContainer>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Blood Pressure</CardTitle>
                </CardHeader>
                <CardContent>
                  <ResponsiveContainer width="100%" height={300}>
                    <LineChart data={getChartData('bloodPressure')}>
                      <CartesianGrid strokeDasharray="3 3" />
                      <XAxis dataKey="date" />
                      <YAxis />
                      <Tooltip />
                      <Line type="monotone" dataKey="value" stroke="#82ca9d" strokeWidth={2} />
                    </LineChart>
                  </ResponsiveContainer>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="appointments" className="space-y-6">
            <div className="flex justify-between items-center">
              <h3 className="text-lg font-semibold">Upcoming Appointments</h3>
              <Link href="/appointments">
                <Button>Book New Appointment</Button>
              </Link>
            </div>
            
            <div className="space-y-4">
              {getUpcomingAppointments().map((appointment) => (
                <Card key={appointment.$id}>
                  <CardContent className="p-4">
                    <div className="flex justify-between items-center">
                      <div>
                        <h4 className="font-semibold">{appointment.type}</h4>
                        <p className="text-sm text-gray-600">Dr. {appointment.doctor}</p>
                        <p className="text-sm text-gray-500">
                          {new Date(appointment.date).toLocaleDateString()} at {appointment.time}
                        </p>
                      </div>
                      <Badge variant="outline">
                        <Clock className="w-4 h-4 mr-1" />
                        Upcoming
                      </Badge>
                    </div>
                  </CardContent>
                </Card>
              ))}
              
              {getUpcomingAppointments().length === 0 && (
                <Card>
                  <CardContent className="p-8 text-center">
                    <Calendar className="w-12 h-12 mx-auto text-gray-400 mb-4" />
                    <h3 className="text-lg font-semibold mb-2">No Upcoming Appointments</h3>
                    <p className="text-gray-600 mb-4">Schedule your next check-up to stay on track</p>
                    <Link href="/appointments">
                      <Button>Schedule Appointment</Button>
                    </Link>
                  </CardContent>
                </Card>
              )}
            </div>
          </TabsContent>

          <TabsContent value="tips" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Daily Pregnancy Tips</CardTitle>
                <CardDescription>Personalized advice for week {week}</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {pregnancyTips.map((tip, index) => (
                    <div key={index} className="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                      <div className="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        {index + 1}
                      </div>
                      <p className="text-sm">{tip}</p>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Educational Resources</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <Link href="/resources" className="block p-3 border rounded-lg hover:bg-gray-50">
                      <h4 className="font-semibold">Pregnancy Nutrition Guide</h4>
                      <p className="text-sm text-gray-600">Learn about essential nutrients for you and your baby</p>
                    </Link>
                    <Link href="/resources" className="block p-3 border rounded-lg hover:bg-gray-50">
                      <h4 className="font-semibold">Exercise During Pregnancy</h4>
                      <p className="text-sm text-gray-600">Safe workouts for expectant mothers</p>
                    </Link>
                    <Link href="/resources" className="block p-3 border rounded-lg hover:bg-gray-50">
                      <h4 className="font-semibold">Preparing for Labor</h4>
                      <p className="text-sm text-gray-600">What to expect and how to prepare</p>
                    </Link>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Reminders</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <div className="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                      <div>
                        <h4 className="font-semibold text-yellow-800">Prenatal Vitamin</h4>
                        <p className="text-sm text-yellow-600">Take with breakfast</p>
                      </div>
                      <Badge variant="outline" className="text-yellow-600 border-yellow-300">
                        Daily
                      </Badge>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <div>
                        <h4 className="font-semibold text-blue-800">Hydration Check</h4>
                        <p className="text-sm text-blue-600">Drink 8 glasses of water</p>
                      </div>
                      <Badge variant="outline" className="text-blue-600 border-blue-300">
                        Daily
                      </Badge>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                      <div>
                        <h4 className="font-semibold text-green-800">Gentle Exercise</h4>
                        <p className="text-sm text-green-600">30 minutes of walking</p>
                      </div>
                      <Badge variant="outline" className="text-green-600 border-green-300">
                        3x/week
                      </Badge>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
}
