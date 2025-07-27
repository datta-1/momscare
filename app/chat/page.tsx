"use client"

import { useState, useRef } from "react"
import { ToastContainer, toast } from "react-toastify"
import { motion, AnimatePresence } from "framer-motion"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card } from "@/components/ui/card"
import { ScrollArea } from "@/components/ui/scroll-area"
import ReactMarkdown from "react-markdown"
import "react-toastify/dist/ReactToastify.css"

interface Message {
  id: number
  text: string
  sender: "user" | "bot"
  timestamp: Date
}

export default function Chat() {
  const [userFeeling, setUserFeeling] = useState("")
  const [age, setAge] = useState("")
  const [weeksPregnant, setWeeksPregnant] = useState("")
  const [preExistingConditions, setPreExistingConditions] = useState("")
  const [specificConcerns, setSpecificConcerns] = useState("")
  const [showChat, setShowChat] = useState(false)
  const [messages, setMessages] = useState<Message[]>([
    {
      id: 1,
      text: "Hello! I'm your AI pregnancy care assistant. How can I help you today?",
      sender: "bot",
      timestamp: new Date(),
    },
  ])
  const [input, setInput] = useState("")
  const [loading, setLoading] = useState(false)
  const [userName, setUserName] = useState("User")
  const [userLocation, setUserLocation] = useState("")
  const [isLocating, setIsLocating] = useState(false)

  const messagesEndRef = useRef<HTMLDivElement>(null)

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" })
  }

  const getCurrentLocation = () => {
    setIsLocating(true)
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const { latitude, longitude } = position.coords
          try {
            // Use a free geocoding service to get location name
            const response = await fetch(
              `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`
            )
            const data = await response.json()
            const location = `${data.city}, ${data.principalSubdivision}, ${data.countryName}`
            setUserLocation(location)
            toast.success(`Location detected: ${location}`)
          } catch (error) {
            setUserLocation(`${latitude.toFixed(2)}, ${longitude.toFixed(2)}`)
            toast.info("Location coordinates detected")
          }
          setIsLocating(false)
        },
        (error) => {
          toast.error("Unable to detect location. Please enter manually if needed.")
          setIsLocating(false)
        }
      )
    } else {
      toast.error("Geolocation is not supported by this browser.")
      setIsLocating(false)
    }
  }

  const sendMessage = async (messageText: string) => {
    if (!messageText.trim()) return

    const userMessage: Message = {
      id: Date.now(),
      text: messageText,
      sender: "user",
      timestamp: new Date(),
    }

    setMessages((prev) => [...prev, userMessage])
    setInput("")
    setLoading(true)

    try {
      // Build context for AI
      let context = `User Information:
Name: ${userName}
Age: ${age || "Not specified"}
Weeks pregnant: ${weeksPregnant || "Not specified"}
Current feeling: ${userFeeling || "Not specified"}
Pre-existing conditions: ${preExistingConditions || "None specified"}
Specific concerns: ${specificConcerns || "None specified"}
Location: ${userLocation || "Not specified"}

Medical Documents: No documents available in demo mode.

Please provide helpful, accurate, and supportive advice for this pregnant user. Always recommend consulting with healthcare professionals for serious concerns.`

      const response = await fetch("/api/chat", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          message: messageText,
          context: context,
        }),
      })

      if (!response.ok) {
        throw new Error("Failed to get response")
      }

      const data = await response.json()

      const botMessage: Message = {
        id: Date.now() + 1,
        text: data.response,
        sender: "bot",
        timestamp: new Date(),
      }

      setMessages((prev) => [...prev, botMessage])
    } catch (error) {
      toast.error("Failed to send message. Please try again.")
      console.error("Error:", error)
    } finally {
      setLoading(false)
      setTimeout(scrollToBottom, 100)
    }
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    sendMessage(input)
  }

  const startChat = () => {
    if (!userFeeling || !age || !weeksPregnant) {
      toast.error("Please fill in the required fields (feeling, age, weeks pregnant)")
      return
    }
    setShowChat(true)
    setTimeout(scrollToBottom, 100)
  }

  if (!showChat) {
    return (
      <div className="min-h-screen flex items-center justify-center p-4">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="w-full max-w-2xl"
        >
          <Card className="p-8">
            <h1 className="text-3xl font-bold text-center mb-8">
              MomCare AI Chat
            </h1>
            <div className="space-y-6">
              <div>
                <label className="block text-sm font-medium mb-2">
                  How are you feeling today? *
                </label>
                <Input
                  value={userFeeling}
                  onChange={(e) => setUserFeeling(e.target.value)}
                  placeholder="e.g., tired, nauseous, excited..."
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">
                  Your age *
                </label>
                <Input
                  type="number"
                  value={age}
                  onChange={(e) => setAge(e.target.value)}
                  placeholder="e.g., 28"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">
                  Weeks pregnant *
                </label>
                <Input
                  type="number"
                  value={weeksPregnant}
                  onChange={(e) => setWeeksPregnant(e.target.value)}
                  placeholder="e.g., 12"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">
                  Pre-existing conditions (optional)
                </label>
                <Input
                  value={preExistingConditions}
                  onChange={(e) => setPreExistingConditions(e.target.value)}
                  placeholder="e.g., diabetes, hypertension..."
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">
                  Specific concerns (optional)
                </label>
                <Input
                  value={specificConcerns}
                  onChange={(e) => setSpecificConcerns(e.target.value)}
                  placeholder="e.g., morning sickness, back pain..."
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">
                  Location (optional)
                </label>
                <div className="flex gap-2">
                  <Input
                    value={userLocation}
                    onChange={(e) => setUserLocation(e.target.value)}
                    placeholder="Enter your location or use GPS"
                  />
                  <Button
                    type="button"
                    variant="outline"
                    onClick={getCurrentLocation}
                    disabled={isLocating}
                  >
                    {isLocating ? "Getting..." : "Use GPS"}
                  </Button>
                </div>
              </div>
              <Button onClick={startChat} className="w-full">
                Start Chatting
              </Button>
            </div>
          </Card>
        </motion.div>
      </div>
    )
  }

  return (
    <div className="min-h-screen flex flex-col">
      <div className="flex-1 flex flex-col max-w-4xl mx-auto w-full p-4">
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg flex-1 flex flex-col">
          <div className="p-4 border-b">
            <h2 className="text-xl font-semibold">Chat with MomCare AI</h2>
            <p className="text-sm text-gray-600 dark:text-gray-400">
              Your AI pregnancy care assistant
            </p>
          </div>

          <ScrollArea className="flex-1 p-4">
            <div className="space-y-4">
              <AnimatePresence>
                {messages.map((message) => (
                  <motion.div
                    key={message.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -20 }}
                    className={`flex ${
                      message.sender === "user" ? "justify-end" : "justify-start"
                    }`}
                  >
                    <div
                      className={`max-w-[80%] p-3 rounded-lg ${
                        message.sender === "user"
                          ? "bg-primary text-primary-foreground"
                          : "bg-muted"
                      }`}
                    >
                      <ReactMarkdown className="prose prose-sm dark:prose-invert">
                        {message.text}
                      </ReactMarkdown>
                      <div className="text-xs opacity-70 mt-2">
                        {message.timestamp.toLocaleTimeString()}
                      </div>
                    </div>
                  </motion.div>
                ))}
              </AnimatePresence>
              {loading && (
                <motion.div
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  className="flex justify-start"
                >
                  <div className="bg-muted p-3 rounded-lg">
                    <div className="flex space-x-1">
                      <div className="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></div>
                      <div className="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style={{ animationDelay: "0.1s" }}></div>
                      <div className="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style={{ animationDelay: "0.2s" }}></div>
                    </div>
                  </div>
                </motion.div>
              )}
              <div ref={messagesEndRef} />
            </div>
          </ScrollArea>

          <div className="p-4 border-t">
            <form onSubmit={handleSubmit} className="flex space-x-2">
              <Input
                value={input}
                onChange={(e) => setInput(e.target.value)}
                placeholder="Type your message..."
                disabled={loading}
                className="flex-1"
              />
              <Button type="submit" disabled={loading || !input.trim()}>
                Send
              </Button>
            </form>
          </div>
        </div>
      </div>
      <ToastContainer position="bottom-right" />
    </div>
  )
}