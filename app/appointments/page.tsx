"use client"

import { useState, useEffect } from 'react'
import { Calendar } from '@/components/ui/calendar'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { toast, ToastContainer } from 'react-toastify'
import { Calendar as CalendarIcon, Clock, MapPin, Phone, User, Stethoscope, Plus, Edit, Trash2 } from 'lucide-react'
import { createAppointment, listAppointments, updateAppointment, deleteAppointment, getCurrentUser } from '@/lib/appwrite'
import 'react-toastify/dist/ReactToastify.css'

const timeSlots = [
  "09:00 AM", "09:30 AM", "10:00 AM", "10:30 AM", "11:00 AM", "11:30 AM",
  "02:00 PM", "02:30 PM", "03:00 PM", "03:30 PM", "04:00 PM", "04:30 PM", "05:00 PM"
]

const appointmentTypes = [
  "Regular Checkup",
  "Ultrasound",
  "Blood Test",
  "Genetic Counseling",
  "Nutrition Consultation",
  "Mental Health Support",
  "Emergency Consultation"
]

const doctors = [
  { id: 1, name: "Dr. Sarah Johnson", specialty: "Obstetrician", phone: "+1 234-567-8901" },
  { id: 2, name: "Dr. Michael Chen", specialty: "Perinatologist", phone: "+1 234-567-8902" },
  { id: 3, name: "Dr. Emily Rodriguez", specialty: "Midwife", phone: "+1 234-567-8903" },
  { id: 4, name: "Dr. James Wilson", specialty: "Gynecologist", phone: "+1 234-567-8904" }
]

interface Appointment {
  $id?: string;
  date: string;
  time: string;
  type: string;
  doctor: string;
  doctorPhone?: string;
  notes?: string;
  status?: 'scheduled' | 'completed' | 'cancelled';
  userId?: string;
  createdAt?: string;
}

export default function Appointments() {
  const [date, setDate] = useState<Date | undefined>(undefined)
  const [selectedTime, setSelectedTime] = useState<string>("")
  const [appointmentType, setAppointmentType] = useState<string>("")
  const [selectedDoctor, setSelectedDoctor] = useState<string>("")
  const [notes, setNotes] = useState<string>("")
  const [appointments, setAppointments] = useState<Appointment[]>([])
  const [loading, setLoading] = useState(false)
  const [editingAppointment, setEditingAppointment] = useState<Appointment | null>(null)
  const [user, setUser] = useState<any>(null)

  useEffect(() => {
    loadUserAndAppointments()
  }, [])

  const loadUserAndAppointments = async () => {
    try {
      const currentUser = await getCurrentUser()
      if (currentUser) {
        setUser(currentUser)
        const appointmentsData = await listAppointments()
        setAppointments(appointmentsData.documents || [])
      }
    } catch (error) {
      console.error('Error loading data:', error)
      toast.error('Error loading appointments')
    }
  }

  const handleBookAppointment = async () => {
    if (!date || !selectedTime || !appointmentType || !selectedDoctor) {
      toast.error("Please fill in all required fields")
      return
    }

    if (!user) {
      toast.error("Please login to book an appointment")
      return
    }

    setLoading(true)
    
    try {
      const selectedDoctorData = doctors.find(doc => doc.name === selectedDoctor)
      
      const appointmentData = {
        date: date.toISOString().split('T')[0],
        time: selectedTime,
        type: appointmentType,
        doctor: selectedDoctor,
        doctorPhone: selectedDoctorData?.phone || '',
        notes: notes || '',
        status: 'scheduled'
      }

      if (editingAppointment && editingAppointment.$id) {
        // Update existing appointment
        await updateAppointment(editingAppointment.$id, appointmentData)
        toast.success("Appointment updated successfully!")
      } else {
        // Create new appointment
        await createAppointment(appointmentData)
        toast.success("Appointment booked successfully!")
      }

      // Reset form
      setDate(undefined)
      setSelectedTime("")
      setAppointmentType("")
      setSelectedDoctor("")
      setNotes("")
      setEditingAppointment(null)
      
      // Reload appointments
      loadUserAndAppointments()
    } catch (error) {
      console.error('Error booking appointment:', error)
      toast.error("Failed to book appointment")
    } finally {
      setLoading(false)
    }
  }

  const handleEditAppointment = (appointment: Appointment) => {
    setEditingAppointment(appointment)
    setDate(new Date(appointment.date))
    setSelectedTime(appointment.time)
    setAppointmentType(appointment.type)
    setSelectedDoctor(appointment.doctor)
    setNotes(appointment.notes || "")
  }

  const handleCancelEdit = () => {
    setEditingAppointment(null)
    setDate(undefined)
    setSelectedTime("")
    setAppointmentType("")
    setSelectedDoctor("")
    setNotes("")
  }

  const handleDeleteAppointment = async (appointmentId: string) => {
    if (!confirm("Are you sure you want to delete this appointment?")) {
      return
    }

    try {
      await deleteAppointment(appointmentId)
      toast.success("Appointment deleted successfully!")
      loadUserAndAppointments()
    } catch (error) {
      console.error('Error deleting appointment:', error)
      toast.error("Failed to delete appointment")
    }
  }

  const getUpcomingAppointments = () => {
    const today = new Date()
    return appointments.filter(apt => new Date(apt.date) >= today).sort((a, b) => 
      new Date(a.date).getTime() - new Date(b.date).getTime()
    )
  }

  const getPastAppointments = () => {
    const today = new Date()
    return appointments.filter(apt => new Date(apt.date) < today).sort((a, b) => 
      new Date(b.date).getTime() - new Date(a.date).getTime()
    )
  }

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'scheduled': return 'bg-blue-100 text-blue-800 border-blue-200'
      case 'completed': return 'bg-green-100 text-green-800 border-green-200'
      case 'cancelled': return 'bg-red-100 text-red-800 border-red-200'
      default: return 'bg-gray-100 text-gray-800 border-gray-200'
    }
  }

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <Card className="w-full max-w-md">
          <CardContent className="p-8 text-center">
            <h2 className="text-2xl font-bold mb-4">Authentication Required</h2>
            <p className="text-gray-600 mb-6">Please log in to manage your appointments</p>
            <Button asChild>
              <a href="/login">Login</a>
            </Button>
          </CardContent>
        </Card>
      </div>
    )
  }

  return (
    <div className="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-purple-50">
      <ToastContainer position="top-right" autoClose={3000} />
      
      <div className="max-w-7xl mx-auto">
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            Appointment Management
          </h1>
          <p className="text-xl text-gray-600">
            Schedule and manage your prenatal appointments
          </p>
        </div>

        <Tabs defaultValue="book" className="space-y-8">
          <TabsList className="grid w-full grid-cols-3">
            <TabsTrigger value="book" className="flex items-center gap-2">
              <Plus className="w-4 h-4" />
              {editingAppointment ? 'Edit Appointment' : 'Book Appointment'}
            </TabsTrigger>
            <TabsTrigger value="upcoming" className="flex items-center gap-2">
              <CalendarIcon className="w-4 h-4" />
              Upcoming ({getUpcomingAppointments().length})
            </TabsTrigger>
            <TabsTrigger value="history" className="flex items-center gap-2">
              <Clock className="w-4 h-4" />
              History ({getPastAppointments().length})
            </TabsTrigger>
          </TabsList>

          <TabsContent value="book">
            <div className="grid lg:grid-cols-2 gap-8">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <CalendarIcon className="w-5 h-5" />
                    Select Date & Time
                  </CardTitle>
                  <CardDescription>
                    Choose your preferred date and time slot
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div>
                    <Label className="text-base font-medium">Date</Label>
                    <Calendar
                      mode="single"
                      selected={date}
                      onSelect={setDate}
                      className="rounded-md border mt-2"
                      disabled={(date) => date < new Date() || date.getDay() === 0} // Disable past dates and Sundays
                    />
                  </div>
                  
                  <div>
                    <Label className="text-base font-medium">Time Slot</Label>
                    <Select onValueChange={setSelectedTime} value={selectedTime}>
                      <SelectTrigger className="mt-2">
                        <SelectValue placeholder="Select time slot" />
                      </SelectTrigger>
                      <SelectContent>
                        {timeSlots.map((time) => (
                          <SelectItem key={time} value={time}>
                            {time}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Stethoscope className="w-5 h-5" />
                    Appointment Details
                  </CardTitle>
                  <CardDescription>
                    Provide details about your appointment
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                  <div>
                    <Label className="text-base font-medium">Appointment Type</Label>
                    <Select onValueChange={setAppointmentType} value={appointmentType}>
                      <SelectTrigger className="mt-2">
                        <SelectValue placeholder="Select appointment type" />
                      </SelectTrigger>
                      <SelectContent>
                        {appointmentTypes.map((type) => (
                          <SelectItem key={type} value={type}>
                            {type}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label className="text-base font-medium">Preferred Doctor</Label>
                    <Select onValueChange={setSelectedDoctor} value={selectedDoctor}>
                      <SelectTrigger className="mt-2">
                        <SelectValue placeholder="Select doctor" />
                      </SelectTrigger>
                      <SelectContent>
                        {doctors.map((doctor) => (
                          <SelectItem key={doctor.id} value={doctor.name}>
                            <div className="flex flex-col items-start">
                              <span className="font-medium">{doctor.name}</span>
                              <span className="text-sm text-gray-500">{doctor.specialty}</span>
                            </div>
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>

                  <div>
                    <Label className="text-base font-medium">Notes</Label>
                    <Textarea
                      placeholder="Add any notes or specific concerns for your appointment..."
                      value={notes}
                      onChange={(e) => setNotes(e.target.value)}
                      className="min-h-[100px] mt-2"
                    />
                  </div>

                  {date && selectedTime && appointmentType && selectedDoctor && (
                    <Card className="bg-blue-50 border-blue-200">
                      <CardContent className="p-4">
                        <h4 className="font-semibold text-blue-900 mb-2">Appointment Summary:</h4>
                        <div className="space-y-1 text-sm text-blue-800">
                          <p><strong>Date:</strong> {date.toLocaleDateString()}</p>
                          <p><strong>Time:</strong> {selectedTime}</p>
                          <p><strong>Type:</strong> {appointmentType}</p>
                          <p><strong>Doctor:</strong> {selectedDoctor}</p>
                          {notes && <p><strong>Notes:</strong> {notes}</p>}
                        </div>
                      </CardContent>
                    </Card>
                  )}

                  <div className="flex gap-3">
                    <Button
                      className="flex-1"
                      onClick={handleBookAppointment}
                      disabled={!date || !selectedTime || !appointmentType || !selectedDoctor || loading}
                    >
                      {loading ? 'Processing...' : editingAppointment ? 'Update Appointment' : 'Book Appointment'}
                    </Button>
                    {editingAppointment && (
                      <Button variant="outline" onClick={handleCancelEdit}>
                        Cancel Edit
                      </Button>
                    )}
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="upcoming">
            <div className="space-y-4">
              {getUpcomingAppointments().length === 0 ? (
                <Card>
                  <CardContent className="p-12 text-center">
                    <CalendarIcon className="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 className="text-xl font-semibold mb-2">No Upcoming Appointments</h3>
                    <p className="text-gray-600 mb-6">Schedule your next prenatal checkup to stay on track</p>
                    <Button onClick={() => document.querySelector('[value="book"]')?.click()}>
                      Book Your First Appointment
                    </Button>
                  </CardContent>
                </Card>
              ) : (
                getUpcomingAppointments().map((appointment) => (
                  <Card key={appointment.$id} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-6">
                      <div className="flex justify-between items-start">
                        <div className="flex-1">
                          <div className="flex items-center gap-3 mb-3">
                            <h3 className="text-xl font-semibold">{appointment.type}</h3>
                            <Badge className={getStatusColor(appointment.status || 'scheduled')}>
                              {appointment.status || 'scheduled'}
                            </Badge>
                          </div>
                          
                          <div className="grid md:grid-cols-2 gap-4 text-sm">
                            <div className="flex items-center gap-2">
                              <CalendarIcon className="w-4 h-4 text-gray-500" />
                              <span>{new Date(appointment.date).toLocaleDateString()}</span>
                            </div>
                            <div className="flex items-center gap-2">
                              <Clock className="w-4 h-4 text-gray-500" />
                              <span>{appointment.time}</span>
                            </div>
                            <div className="flex items-center gap-2">
                              <User className="w-4 h-4 text-gray-500" />
                              <span>{appointment.doctor}</span>
                            </div>
                            {appointment.doctorPhone && (
                              <div className="flex items-center gap-2">
                                <Phone className="w-4 h-4 text-gray-500" />
                                <span>{appointment.doctorPhone}</span>
                              </div>
                            )}
                          </div>
                          
                          {appointment.notes && (
                            <div className="mt-3 p-3 bg-gray-50 rounded-lg">
                              <p className="text-sm text-gray-600">{appointment.notes}</p>
                            </div>
                          )}
                        </div>
                        
                        <div className="flex gap-2 ml-4">
                          <Button 
                            size="sm" 
                            variant="outline"
                            onClick={() => handleEditAppointment(appointment)}
                          >
                            <Edit className="w-4 h-4" />
                          </Button>
                          <Button 
                            size="sm" 
                            variant="destructive"
                            onClick={() => appointment.$id && handleDeleteAppointment(appointment.$id)}
                          >
                            <Trash2 className="w-4 h-4" />
                          </Button>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))
              )}
            </div>
          </TabsContent>

          <TabsContent value="history">
            <div className="space-y-4">
              {getPastAppointments().length === 0 ? (
                <Card>
                  <CardContent className="p-12 text-center">
                    <Clock className="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 className="text-xl font-semibold mb-2">No Past Appointments</h3>
                    <p className="text-gray-600">Your appointment history will appear here</p>
                  </CardContent>
                </Card>
              ) : (
                getPastAppointments().map((appointment) => (
                  <Card key={appointment.$id} className="opacity-75">
                    <CardContent className="p-6">
                      <div className="flex justify-between items-start">
                        <div className="flex-1">
                          <div className="flex items-center gap-3 mb-3">
                            <h3 className="text-xl font-semibold">{appointment.type}</h3>
                            <Badge className={getStatusColor(appointment.status || 'completed')}>
                              {appointment.status || 'completed'}
                            </Badge>
                          </div>
                          
                          <div className="grid md:grid-cols-2 gap-4 text-sm">
                            <div className="flex items-center gap-2">
                              <CalendarIcon className="w-4 h-4 text-gray-500" />
                              <span>{new Date(appointment.date).toLocaleDateString()}</span>
                            </div>
                            <div className="flex items-center gap-2">
                              <Clock className="w-4 h-4 text-gray-500" />
                              <span>{appointment.time}</span>
                            </div>
                            <div className="flex items-center gap-2">
                              <User className="w-4 h-4 text-gray-500" />
                              <span>{appointment.doctor}</span>
                            </div>
                          </div>
                          
                          {appointment.notes && (
                            <div className="mt-3 p-3 bg-gray-50 rounded-lg">
                              <p className="text-sm text-gray-600">{appointment.notes}</p>
                            </div>
                          )}
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))
              )}
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  )
}