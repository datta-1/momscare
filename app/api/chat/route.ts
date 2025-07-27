import { NextRequest, NextResponse } from 'next/server'

export async function POST(request: NextRequest) {
  try {
    const { message, context } = await request.json()

    // For demo purposes, we'll create a simple response
    // In a real application, you would integrate with an AI service like OpenAI, Gemini, etc.
    
    let response = ""
    
    // Simple keyword-based responses for demo
    if (message.toLowerCase().includes('nausea') || message.toLowerCase().includes('morning sickness')) {
      response = "Morning sickness is very common during early pregnancy. Here are some tips:\n\n• Eat small, frequent meals\n• Try ginger tea or ginger supplements\n• Avoid strong smells that trigger nausea\n• Get plenty of rest\n• Stay hydrated\n\nIf symptoms are severe, please consult your healthcare provider."
    } else if (message.toLowerCase().includes('exercise') || message.toLowerCase().includes('workout')) {
      response = "Exercise during pregnancy is generally beneficial! Here's what's recommended:\n\n• Light to moderate exercise like walking, swimming, prenatal yoga\n• Avoid contact sports and activities with fall risk\n• Stay hydrated and don't overheat\n• Listen to your body and rest when needed\n\nAlways consult your doctor before starting any new exercise routine during pregnancy."
    } else if (message.toLowerCase().includes('nutrition') || message.toLowerCase().includes('diet') || message.toLowerCase().includes('food')) {
      response = "Good nutrition is crucial during pregnancy. Focus on:\n\n• Folic acid-rich foods (leafy greens, citrus fruits)\n• Calcium sources (dairy, fortified foods)\n• Iron-rich foods (lean meats, beans, spinach)\n• Omega-3 fatty acids (fish, walnuts)\n• Plenty of water\n\nAvoid raw fish, unpasteurized products, and limit caffeine. Consider prenatal vitamins as recommended by your healthcare provider."
    } else if (message.toLowerCase().includes('appointment') || message.toLowerCase().includes('checkup')) {
      response = "Regular prenatal appointments are important for monitoring your health and baby's development:\n\n• First trimester: Monthly visits\n• Second trimester: Every 2-4 weeks\n• Third trimester: Weekly visits\n\nTypical appointments include weight checks, blood pressure monitoring, urine tests, and fetal heart rate monitoring. Don't hesitate to call your provider with any concerns between visits."
    } else if (message.toLowerCase().includes('kick') || message.toLowerCase().includes('movement')) {
      response = "Fetal movements are an exciting milestone! Here's what to expect:\n\n• First movements typically felt between 16-25 weeks\n• Initially feels like flutters or bubbles\n• Movements become stronger and more regular over time\n• Baby's activity patterns may change throughout the day\n\nIf you notice a significant decrease in movements, contact your healthcare provider immediately."
    } else {
      response = `Thank you for your question! Based on your information:\n\n${context.includes('Age:') ? `• You're ${context.match(/Age: ([^\\n]+)/)?.[1]} years old` : ''}\n${context.includes('Weeks pregnant:') ? `• Currently ${context.match(/Weeks pregnant: ([^\\n]+)/)?.[1]} weeks pregnant` : ''}\n${context.includes('Current feeling:') ? `• Feeling: ${context.match(/Current feeling: ([^\\n]+)/)?.[1]}` : ''}\n\nI'm here to help with pregnancy-related questions and concerns. Feel free to ask about:\n• Nutrition and diet\n• Exercise during pregnancy\n• Common pregnancy symptoms\n• Prenatal care and appointments\n• Baby development milestones\n\nFor specific medical concerns, always consult with your healthcare provider. How else can I assist you today?`
    }

    return NextResponse.json({ response })
  } catch (error) {
    console.error('Chat API error:', error)
    return NextResponse.json(
      { error: 'Failed to process your message. Please try again.' },
      { status: 500 }
    )
  }
}